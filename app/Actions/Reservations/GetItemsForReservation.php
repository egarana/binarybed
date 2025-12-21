<?php

namespace App\Actions\Reservations;

use App\HandlesTenancy;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GetItemsForReservation
{
    use HandlesTenancy;

    /**
     * Get all items for a specific reservation with pagination, filters, and sorting.
     *
     * @param string $tenantId
     * @param string $reservationCode
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function execute(string $tenantId, string $reservationCode, int $perPage = 10): LengthAwarePaginator
    {
        return $this->executeInTenantContext($tenantId, function () use ($reservationCode, $perPage) {
            $reservation = Reservation::where('code', $reservationCode)->firstOrFail();

            $query = ReservationItem::where('reservation_id', $reservation->id);

            // Apply status filter with validation
            $statusFilter = request()->input('status');
            if ($statusFilter !== null && $statusFilter !== '') {
                // Validate status value (only active or cancelled allowed)
                $allowedStatuses = ['active', 'cancelled', 'ACTIVE', 'CANCELLED'];
                if (in_array($statusFilter, $allowedStatuses)) {
                    $query->where('status', strtoupper($statusFilter));
                }
            }

            // Apply resource type filter with validation
            $resourceTypeFilter = request()->input('resource_type');
            if ($resourceTypeFilter !== null && $resourceTypeFilter !== '') {
                // Validate resource type (only Room or Activity allowed)
                $allowedTypes = ['Room', 'Activity'];
                if (in_array($resourceTypeFilter, $allowedTypes)) {
                    $query->where('resource_type_label', $resourceTypeFilter);
                }
            }

            // Apply search filter
            $searchValue = request()->input('search');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('resource_name', 'like', "%{$searchValue}%")
                        ->orWhere('rate_name', 'like', "%{$searchValue}%");
                });
            }

            // Apply sorting
            $sortField = request()->input('sort', '-created_at');
            $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
            $sortField = ltrim($sortField, '-');

            // Validate sort field
            $allowedSorts = [
                'resource_name',
                'rate_name',
                'start_date',
                'end_date',
                'quantity',
                'line_total',
                'status',
                'created_at',
                'updated_at',
            ];
            if (!in_array($sortField, $allowedSorts)) {
                $sortField = 'created_at';
            }

            $query->orderBy($sortField, $sortDirection);

            $currentPage = Paginator::resolveCurrentPage();
            $total = $query->count();
            $items = $query->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

            return new LengthAwarePaginator(
                $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'resource_name' => $item->resource_name,
                        'resource_type_label' => $item->resource_type_label,
                        'rate_name' => $item->rate_name,
                        'pricing_type' => $item->pricing_type,
                        'start_date' => $item->start_date?->format('Y-m-d'),
                        'end_date' => $item->end_date?->format('Y-m-d'),
                        'duration_days' => $item->duration_days,
                        'duration_minutes' => $item->duration_minutes,
                        'quantity' => $item->quantity,
                        'rate_price' => $item->rate_price,
                        'currency' => $item->currency,
                        'line_total' => $item->line_total,
                        'status' => $item->status,
                        'formatted_duration' => $item->formatted_duration,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        });
    }
}
