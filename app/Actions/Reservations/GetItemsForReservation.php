<?php

namespace App\Actions\Reservations;

use App\HandlesTenancy;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

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
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($reservationCode, $perPage) {
            $reservation = Reservation::where('code', $reservationCode)->firstOrFail();

            $query = ReservationItem::where('reservation_id', $reservation->id);

            // Apply search filter - supports word-by-word matching
            $searchValue = request()->input('search');
            if ($searchValue) {
                // Split search into words and search each word
                $words = preg_split('/\s+/', trim($searchValue));
                foreach ($words as $word) {
                    if (strlen($word) > 0) {
                        $query->where(function ($q) use ($word) {
                            $q->where('resource_name', 'like', "%{$word}%")
                                ->orWhere('rate_name', 'like', "%{$word}%");
                        });
                    }
                }
            }

            // Apply sorting
            $sortField = request()->input('sort', '-created_at');
            $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
            $sortField = ltrim($sortField, '-');

            // Special handling for duration sorting (calculated field)
            if ($sortField === 'duration') {
                $query->orderByRaw("DATEDIFF(end_date, start_date) {$sortDirection}");
            } else {
                // Map frontend column keys to database columns
                $sortFieldMap = [
                    'product' => 'resource_name',
                    'schedule' => 'start_date',
                    'rate' => 'rate_name',
                    'total' => 'line_total',
                ];

                // Apply mapping if exists
                if (isset($sortFieldMap[$sortField])) {
                    $sortField = $sortFieldMap[$sortField];
                }

                // Validate sort field
                $allowedSorts = [
                    'resource_name',
                    'rate_name',
                    'rate_price',
                    'currency',
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
            }

            $currentPage = Paginator::resolveCurrentPage();
            $total = $query->count();
            $items = $query->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

            return new LengthAwarePaginator(
                $items->map(function ($item) use ($tenant) {
                    // Calculate duration based on resource type only
                    $isUnit = $item->reservable_type === Unit::class || $item->resource_type_label === 'Unit';
                    $duration = $this->calculateDuration($item->start_date, $item->end_date, $isUnit);
                    $durationLabel = $this->formatDurationLabel($duration, $isUnit);

                    return [
                        'id' => $item->id,
                        'resource_name' => $item->resource_name,
                        'resource_type_label' => $item->resource_type_label,
                        'rate_name' => $item->rate_name,
                        'price_type' => $item->price_type,
                        'start_date' => $item->start_date?->format('Y-m-d'),
                        'end_date' => $item->end_date?->format('Y-m-d'),
                        'start_time' => $item->start_time,
                        'end_time' => $item->end_time,
                        'duration' => $duration,
                        'duration_label' => $durationLabel,
                        'quantity' => $item->quantity,
                        'rate_price' => $item->rate_price,
                        'currency' => $item->currency,
                        'line_total' => $item->line_total,
                        'status' => $item->status,
                        'tenant_name' => $tenant->name,
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

    /**
     * Calculate duration based on dates and resource type.
     * Unit = nights (end - start)
     * Activity = days (end - start + 1)
     */
    private function calculateDuration($startDate, $endDate, bool $isUnit): int
    {
        if (!$startDate || !$endDate) {
            return 1;
        }

        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        $diffDays = $start->diffInDays($end);

        // Unit: nights = end - start
        // Activity: days = end - start + 1
        return $isUnit ? max(1, $diffDays) : max(1, $diffDays + 1);
    }

    /**
     * Format duration label for display.
     * Unit = "X nights", Activity = "X days"
     */
    private function formatDurationLabel(int $duration, bool $isUnit): string
    {
        if ($isUnit) {
            return $duration === 1 ? '1 night' : "{$duration} nights";
        }
        return $duration === 1 ? '1 day' : "{$duration} days";
    }
}
