<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Unit;
use App\Models\Activity;
use App\Models\Rate;
use App\HandlesTenancy;

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
            $pricingType = $data['pricing_type'] ?? null;
            $ratePrice = $data['rate_price'] ?? 0;
            $currency = $data['currency'] ?? 'IDR';

            if (isset($data['rate_id'])) {
                $rate = Rate::find($data['rate_id']);

                if ($rate) {
                    // Snapshot rate data
                    $rateName = $rateName ?? $rate->name;
                    $rateDescription = $rateDescription ?? ($rate->description ?? null);
                    $ratePrice = $ratePrice ?: $rate->price;
                    $currency = $rate->currency ?? 'IDR';
                }
            }

            // Calculate line total
            $quantity = $data['quantity'] ?? 1;
            $durationDays = $data['duration_days'] ?? 1;
            $durationMinutes = $data['duration_minutes'] ?? null;

            $lineTotal = $this->calculateLineTotal(
                $pricingType,
                $ratePrice,
                $quantity,
                $durationDays,
                $durationMinutes
            );

            // Create the item with snapshots
            $item = $reservation->items()->create([
                'reservable_type' => $data['reservable_type'] ?? null,
                'reservable_id' => $data['reservable_id'] ?? null,
                'rate_id' => $data['rate_id'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'duration_days' => $durationDays,
                'duration_minutes' => $durationMinutes,
                'quantity' => $quantity,
                // Granular Snapshotting
                'resource_name' => $resourceName,
                'resource_type_label' => $resourceTypeLabel,
                'resource_description' => $resourceDescription,
                'rate_name' => $rateName,
                'rate_description' => $rateDescription,
                'pricing_type' => $pricingType,
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
     * Calculate line total.
     * All pricing types use: quantity × duration_days × rate_price
     */
    private function calculateLineTotal(
        ?string $pricingType,
        int $ratePrice,
        int $quantity,
        int $durationDays,
        ?int $durationMinutes
    ): int {
        return $quantity * $durationDays * $ratePrice;
    }
}
