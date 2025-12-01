<?php

namespace App\Repositories;

use App\HasMultiTenantSearch;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Sorts\RelationSort;
use App\QueryBuilder\Filters\RelationFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnitRepository
{
    use HasMultiTenantSearch;

    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(Unit::class)
            ->allowedFilters([
                'name',
                'slug',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'slug'])),
            ])
            ->allowedSorts([
                'name',
                'slug',
                'created_at',
                'updated_at',
                // AllowedSort::custom('domain', new RelationSort('domains', 'domain', 'MIN')),
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
        $collectionFields = ['tenant_name', 'tenant_id'];

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
        $allUnits = $this->fetchFromAllTenants(
            modelClass: Unit::class,
            callback: fn() => $needsCollectionSearch,
            queryModifier: fn($query) => $this->baseQuery(),
            tenantDataMapper: function ($unit, $tenant) {
                $unitArray = $unit->toArray();
                $unitArray['tenant_id'] = $tenant->id;
                $unitArray['tenant_name'] = $tenant->name ?? $tenant->id;
                return $unitArray;
            }
        );

        // Restore original sort parameter if it was removed
        if ($originalSort !== null) {
            $request->merge(['sort' => $originalSort]);
        }

        // Apply collection-level search if needed
        if ($searchValue && $needsCollectionSearch) {
            $allUnits = $this->applyCollectionSearch($allUnits, $searchValue, $searchFields);
        }

        // Apply sorting manually (data is merged from multiple databases)
        $sortField = $request->input('sort', 'name');
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $allUnits = $allUnits->sortBy($sortField, SORT_REGULAR, $sortDirection === 'desc');

        // Manual pagination to maintain Laravel pagination format
        $currentPage = Paginator::resolveCurrentPage();
        $currentPageItems = $allUnits->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $result = new LengthAwarePaginator(
            $currentPageItems,
            $allUnits->count(),
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

    public function getForEdit(Unit $unit): Unit
    {
        return $unit;
    }

    public function create(array $data): Unit
    {
        $unit = Unit::create($data);

        return $unit;
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }

    /**
     * Get all users attached to a unit.
     *
     * @param Unit $unit
     * @return array
     */
    public function getAttachedUsers(Unit $unit): array
    {
        return $unit->users()
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'global_id' => $user->global_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'assigned_at' => $user->pivot->assigned_at ? Carbon::parse($user->pivot->assigned_at) : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get paginated users attached to a unit with filtering and sorting support.
     *
     * @param Unit $unit
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAttachedUsersPaginated(Unit $unit, Request $request): LengthAwarePaginator
    {
        $perPage = $this->pagination->resolvePerPage($request);

        // Pre-process search parameters for QueryBuilder
        $searchValue = $request->input('search');
        if ($searchValue) {
            $request->merge(['filter' => array_merge(
                $request->input('filter', []),
                ['search' => $searchValue]
            )]);
        }

        // Get the base relationship query
        $relationQuery = $unit->users();

        // Build the QueryBuilder with filters and sorts
        $query = QueryBuilder::for($relationQuery)
            ->allowedFilters([
                'name',
                'email',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'email'])),
            ])
            ->allowedSorts([
                'name',
                'email',
                'assigned_at', // This works because the pivot is already joined in the relationship
            ])
            ->defaultSort('-assigned_at');

        // Get paginated results
        $users = $query->paginate($perPage);

        // Transform the data to include pivot information
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'global_id' => $user->global_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'assigned_at' => $user->pivot->assigned_at ? Carbon::parse($user->pivot->assigned_at) : null,
            ];
        });

        return $users;
    }

    /**
     * Search units for combobox (with limit).
     * Returns units in format: [{ value: id, label: name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Unit::query();

        // If no search term, return first N units
        if (!$search) {
            return $query->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn($unit) => [
                    'value' => $unit->id,
                    'label' => $unit->name,
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
            ->map(fn($unit) => [
                'value' => $unit->id,
                'label' => $unit->name,
            ])
            ->toArray();
    }
}
