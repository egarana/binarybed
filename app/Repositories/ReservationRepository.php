<?php

namespace App\Repositories;

use App\HasMultiTenantSearch;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservationRepository
{
    use HasMultiTenantSearch;

    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(Reservation::class)
            ->allowedFilters([
                'code',
                'guest_name',
                'guest_email',
                'status',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['code', 'guest_name', 'guest_email'])),
            ])
            ->allowedSorts([
                'code',
                'guest_name',
                'status',
                'total_amount',
                'items_count',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at');
    }

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        $perPage = $this->pagination->resolvePerPage($request);

        // Capture search parameters
        $searchValue = $request->input('search');
        $searchFields = $request->input('fields')
            ? explode(',', $request->input('fields'))
            : ['code', 'guest_name', 'guest_email'];

        // Define fields that only exist at collection level (added post-query)
        $collectionFields = ['tenant_name', 'tenant_id', 'items_count'];

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
        $sortField = $request->input('sort', '-created_at');
        $sortFieldName = ltrim($sortField, '-');
        $isCollectionSort = in_array($sortFieldName, $collectionFields);

        // If sorting by collection field, temporarily remove sort parameter for QueryBuilder
        $originalSort = null;
        if ($isCollectionSort) {
            $originalSort = $request->input('sort');
            $request->offsetUnset('sort');
        }

        // Fetch data from all tenant databases using the trait
        $allReservations = $this->fetchFromAllTenants(
            modelClass: Reservation::class,
            callback: fn() => $needsCollectionSearch,
            queryModifier: fn($query) => $this->baseQuery(),
            tenantDataMapper: function ($reservation, $tenant) {
                $reservation->load('items');
                $reservationArray = $reservation->toArray();
                $reservationArray['tenant_id'] = $tenant->id;
                $reservationArray['tenant_name'] = $tenant->name ?? $tenant->id;

                // Get active items
                $activeItems = $reservation->items->where('status', 'ACTIVE');

                // Total count for sorting
                $reservationArray['items_count'] = $activeItems->count();

                // Dynamic grouping by resource type (flexible for future resource types)
                $itemsByType = $activeItems->groupBy(function ($item) {
                    // Normalize old 'Unit' labels to 'Room' for consistency
                    $label = $item->resource_type_label;
                    if ($label === 'Unit' || empty($label)) {
                        return 'Room';
                    }
                    return $label;
                });

                // Convert to simple count array: ['Room' => 2, 'Activity' => 3, 'Vehicle' => 1, ...]
                $reservationArray['items_by_type'] = $itemsByType->map(fn($items) => $items->count())->toArray();

                return $reservationArray;
            }
        );

        // Restore original sort parameter if it was removed
        if ($originalSort !== null) {
            $request->merge(['sort' => $originalSort]);
        }

        // Apply collection-level search if needed
        if ($searchValue && $needsCollectionSearch) {
            $allReservations = $this->applyCollectionSearch($allReservations, $searchValue, $searchFields);
        }

        // Apply collection-level filters
        $allReservations = $this->applyCollectionFilters($request, $allReservations);

        // Apply sorting manually (data is merged from multiple databases)
        $sortField = $request->input('sort', '-created_at');
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $allReservations = $allReservations->sortBy($sortField, SORT_REGULAR, $sortDirection === 'desc');

        // Manual pagination to maintain Laravel pagination format
        $currentPage = Paginator::resolveCurrentPage();
        $currentPageItems = $allReservations->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $result = new LengthAwarePaginator(
            $currentPageItems,
            $allReservations->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Validate current page
        if ($result->currentPage() > $result->lastPage() && $result->total() > 0) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Apply collection-level filters for fields that are added post-query or need special handling
     */
    private function applyCollectionFilters(Request $request, $collection)
    {
        // Handle status filter (supports multiple comma-separated values)
        $statusFilter = $request->input('status');

        if ($statusFilter !== null && $statusFilter !== '') {
            // Split by comma for multiple values
            $statuses = array_map('trim', explode(',', $statusFilter));

            // Validate against allowed status values
            $allowedStatuses = [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_CONFIRMED,
                Reservation::STATUS_CANCELLED,
                Reservation::STATUS_COMPLETED,
                Reservation::STATUS_NO_SHOW,
            ];

            // Filter out invalid values (protection against URL manipulation)
            $validStatuses = array_filter($statuses, fn($status) => in_array($status, $allowedStatuses));

            // Only apply filter if we have valid statuses
            if (!empty($validStatuses)) {
                $collection = $collection->filter(fn($item) => in_array($item['status'], $validStatuses));
            }
        }

        return $collection;
    }

    public function getForEdit(Reservation $reservation): Reservation
    {
        return $reservation;
    }

    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        $reservation->update($data);

        return $reservation->fresh();
    }

    /**
     * Search reservations for combobox (with limit).
     * Returns reservations in format: [{ value: id, label: code - guest_name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Reservation::query();

        // If no search term, return first N reservations
        if (!$search) {
            return $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($reservation) => [
                    'value' => $reservation->id,
                    'label' => $reservation->code . ' - ' . $reservation->guest_name,
                ])
                ->toArray();
        }

        // Search by code or guest_name
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('guest_name', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($reservation) => [
                'value' => $reservation->id,
                'label' => $reservation->code . ' - ' . $reservation->guest_name,
            ])
            ->toArray();
    }
}
