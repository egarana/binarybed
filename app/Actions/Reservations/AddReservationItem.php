<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Unit;
use App\Models\Activity;
use App\Models\Rate;
use App\HandlesTenancy;
use Carbon\Carbon;

class AddReservationItem
{
    use HandlesTenancy;

    /**
     * Add an item to a reservation with granular snapshotting.
     *
     * @param string $tenantId
     * @param string $reservationCode
     * @param array $data
     * @return ReservationItem
     */
    public function execute(string $tenantId, string $reservationCode, array $data): ReservationItem
    {
        return $this->executeInTenantContext($tenantId, function () use ($reservationCode, $data) {
            $reservation = Reservation::where('code', $reservationCode)->firstOrFail();

            // Get the reservable resource (Unit or Activity)
            $reservable = null;
            $resourceName = $data['resource_name'] ?? null;
            $resourceTypeLabel = $data['resource_type_label'] ?? null;
            $resourceDescription = $data['resource_description'] ?? null;
            $reservableType = $data['reservable_type'] ?? null;

            if (isset($data['reservable_type'], $data['reservable_id'])) {
                $reservableClass = $data['reservable_type'];
                $reservable = $reservableClass::find($data['reservable_id']);

                if ($reservable) {
                    // Snapshot resource data
                    $resourceName = $resourceName ?? $reservable->name;
                    $resourceTypeLabel = $resourceTypeLabel ?? $this->getResourceTypeLabel($reservableClass);
                    $resourceDescription = $resourceDescription ?? ($reservable->description ?? null);
                }
            }

            // Get the rate for snapshotting
            $rateName = $data['rate_name'] ?? null;
            $rateDescription = $data['rate_description'] ?? null;
            $priceType = $data['price_type'] ?? null;
            $ratePrice = $data['rate_price'] ?? 0;
            $currency = $data['currency'] ?? 'IDR';

            if (isset($data['rate_id'])) {
                $rate = Rate::find($data['rate_id']);

                if ($rate) {
                    // Snapshot rate data
                    $rateName = $rateName ?? $rate->name;
                    $rateDescription = $rateDescription ?? ($rate->description ?? null);
                    $priceType = $priceType ?? $rate->price_type;
                    $ratePrice = $ratePrice ?: $rate->price;
                    $currency = $rate->currency ?? 'IDR';
                }
            }

            // Calculate duration from dates based on resource type
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;
            $isUnit = $reservableType === Unit::class;
            $duration = $this->calculateDuration($startDate, $endDate, $isUnit);

            // Calculate line total with duration
            $quantity = $data['quantity'] ?? 1;
            $lineTotal = $this->calculateLineTotal($ratePrice, $quantity, $duration);

            // Create the item with snapshots
            $item = $reservation->items()->create([
                'reservable_type' => $reservableType,
                'reservable_id' => $data['reservable_id'] ?? null,
                'rate_id' => $data['rate_id'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'quantity' => $quantity,
                // Granular Snapshotting
                'resource_name' => $resourceName,
                'resource_type_label' => $resourceTypeLabel,
                'resource_description' => $resourceDescription,
                'rate_name' => $rateName,
                'rate_description' => $rateDescription,
                'price_type' => $priceType,
                'rate_price' => $ratePrice,
                'currency' => $currency,
                'line_total' => $lineTotal,
                'status' => ReservationItem::STATUS_ACTIVE,
            ]);

            // Recalculate reservation totals
            $reservation->recalculateTotals();
            $reservation->save();

            return $item;
        });
    }

    /**
     * Get human-readable label for resource type.
     */
    private function getResourceTypeLabel(string $class): string
    {
        return match ($class) {
            Unit::class => 'Unit',
            Activity::class => 'Activity',
            default => 'Item',
        };
    }

    /**
     * Calculate duration based on dates and resource type.
     * Unit = nights (end - start)
     * Activity = days (end - start + 1)
     */
    private function calculateDuration(?string $startDate, ?string $endDate, bool $isUnit): int
    {
        if (!$startDate || !$endDate) {
            return 1;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $diffDays = $start->diffInDays($end);

        // Unit: nights = end - start
        // Activity: days = end - start + 1
        return $isUnit ? max(1, $diffDays) : max(1, $diffDays + 1);
    }

    /**
     * Calculate line total.
     * Formula: rate_price × quantity × duration
     */
    private function calculateLineTotal(int $ratePrice, int $quantity, int $duration): int
    {
        return $ratePrice * $quantity * $duration;
    }
}
