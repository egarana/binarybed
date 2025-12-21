<?php

namespace App\Repositories;

use App\HasMultiTenantSearch;
use App\Models\Rate;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RateRepository
{
    use HasMultiTenantSearch;

    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(Rate::class)
            ->with('rateable')
            ->allowedFilters([
                'name',
                'slug',
                AllowedFilter::exact('currency'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'slug'])),
            ])
            ->allowedSorts([
                'name',
                'slug',
                'price',
                'currency',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('name');
    }

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        $perPage = $this->pagination->resolvePerPage($request);

        // Capture search parameters
        $searchValue = $request->input('search');
        $searchFields = $request->input('fields')
            ? explode(',', $request->input('fields'))
            : ['name'];

        // Define fields that only exist at collection level (added post-query)
        $collectionFields = ['tenant_name', 'tenant_id', 'resource_name', 'rateable_type', 'product', 'type', 'status'];

        // Check if we need collection-level search
        $needsCollectionSearch = $searchValue && $this->needsCollectionLevelSearch($searchFields, $collectionFields);

        // Get database-only fields for QueryBuilder
        $databaseFields = $this->getDatabaseFields($searchFields, $collectionFields);

        // Prepare request for QueryBuilder if using DB-level filtering
        if ($searchValue && !$needsCollectionSearch && !empty($databaseFields)) {
            $request->merge(['filter' => array_merge(
                $request->input('filter', []),
                ['search' => $searchValue]
            )]);
            $request->merge(['fields' => implode(',', $databaseFields)]);
        }

        // Check if sorting by a collection-level field
        $sortField = $request->input('sort', 'name');
        $sortFieldName = ltrim($sortField, '-');
        $isCollectionSort = in_array($sortFieldName, $collectionFields);

        // If sorting by collection field, temporarily remove sort parameter for QueryBuilder
        $originalSort = null;
        if ($isCollectionSort) {
            $originalSort = $request->input('sort');
            $request->offsetUnset('sort');
        }

        // Fetch data from all tenant databases using the trait
        $allRates = $this->fetchFromAllTenants(
            modelClass: Rate::class,
            callback: fn() => $needsCollectionSearch,
            queryModifier: fn($query) => $this->baseQuery(),
            tenantDataMapper: function ($rate, $tenant) {
                $rateArray = $rate->toArray();
                $rateArray['tenant_id'] = $tenant->id;
                $rateArray['tenant_name'] = $tenant->name ?? $tenant->id;
                $rateArray['resource_name'] = $rate->rateable?->name ?? '';
                $rateArray['resource_slug'] = $rate->rateable?->slug ?? '';
                $rateArray['rateable_type'] = $rate->rateable_type ? class_basename($rate->rateable_type) : '';
                return $rateArray;
            }
        );

        // Restore original sort parameter if it was removed
        if ($originalSort !== null) {
            $request->merge(['sort' => $originalSort]);
        }

        // Apply collection-level search if needed
        if ($searchValue && $needsCollectionSearch) {
            $allRates = $this->applyCollectionSearch($allRates, $searchValue, $searchFields);
        }

        // Apply collection-level filters
        $allRates = $this->applyCollectionFilters($request, $allRates);

        // Apply sorting manually (data is merged from multiple databases)
        $sortField = $request->input('sort', 'name');
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        // Map sort aliases to actual field names
        $sortAliases = [
            'product' => 'resource_name',
            'type' => 'rateable_type',
            'status' => 'is_active',
        ];
        $sortField = $sortAliases[$sortField] ?? $sortField;

        $allRates = $allRates->sortBy($sortField, SORT_REGULAR, $sortDirection === 'desc');

        // Manual pagination to maintain Laravel pagination format
        $currentPage = Paginator::resolveCurrentPage();
        $currentPageItems = $allRates->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $result = new LengthAwarePaginator(
            $currentPageItems,
            $allRates->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Validasi current page
        if ($result->currentPage() > $result->lastPage() && $result->total() > 0) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Apply collection-level filters for fields that are added post-query
     * Supports filter aliases: type -> rateable_type, status -> is_active
     */
    private function applyCollectionFilters(Request $request, $collection)
    {
        // Filter aliases (URL param -> actual field name)
        $filterAliases = [
            'type' => 'rateable_type',
            'status' => 'is_active',
        ];

        // Get filter parameters
        foreach ($filterAliases as $urlParam => $actualField) {
            $filterValue = $request->input($urlParam);

            if ($filterValue !== null && $filterValue !== '') {
                // Handle status filter (convert to boolean comparison)
                if ($urlParam === 'status') {
                    // Validate status value
                    if (!in_array($filterValue, ['active', 'inactive', '1', '0', 'true', 'false'])) {
                        continue; // Skip invalid value
                    }
                    $isActive = $filterValue === 'active' || $filterValue === '1' || $filterValue === 'true';
                    $collection = $collection->filter(fn($item) => $item[$actualField] == $isActive);
                } elseif ($urlParam === 'type') {
                    // Validate type value (only Unit or Activity allowed)
                    $allowedTypes = ['unit', 'activity', 'Unit', 'Activity'];
                    if (!in_array($filterValue, $allowedTypes)) {
                        continue; // Skip invalid value
                    }
                    $collection = $collection->filter(
                        fn($item) => strtolower($item[$actualField] ?? '') === strtolower($filterValue)
                    );
                } else {
                    // Default: case-insensitive match
                    $collection = $collection->filter(
                        fn($item) => strtolower($item[$actualField] ?? '') === strtolower($filterValue)
                    );
                }
            }
        }

        // Handle currency filter at collection level with validation
        $currencyFilter = $request->input('currency');
        if ($currencyFilter !== null && $currencyFilter !== '') {
            // Validate currency code (ISO 4217 common codes)
            $allowedCurrencies = ['IDR', 'USD', 'EUR', 'JPY', 'SGD', 'AUD', 'GBP'];
            if (in_array(strtoupper($currencyFilter), $allowedCurrencies)) {
                $collection = $collection->filter(
                    fn($item) => strtoupper($item['currency'] ?? '') === strtoupper($currencyFilter)
                );
            }
        }

        return $collection;
    }

    public function getForEdit(Rate $rate): Rate
    {
        return $rate;
    }

    public function create(array $data): Rate
    {
        $rate = Rate::create($data);

        return $rate;
    }

    public function update(Rate $rate, array $data): Rate
    {
        $rate->update($data);

        return $rate->fresh();
    }

    public function delete(Rate $rate): void
    {
        $rate->delete();
    }

    /**
     * Search rates for combobox (with limit).
     * Returns rates in format: [{ value: id, label: name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Rate::query();

        // If no search term, return first N rates
        if (!$search) {
            return $query->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn($rate) => [
                    'value' => $rate->id,
                    'label' => $rate->name,
                ])
                ->toArray();
        }

        // Search by name or slug
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($rate) => [
                'value' => $rate->id,
                'label' => $rate->name,
            ])
            ->toArray();
    }
}
