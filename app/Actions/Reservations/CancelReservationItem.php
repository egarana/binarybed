<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\HandlesTenancy;

class CancelReservationItem
{
    use HandlesTenancy;

    /**
     * Cancel an item from a reservation (soft cancel via status).
     *
     * @param string $tenantId
     * @param string $reservationCode
     * @param int $itemId
     * @return ReservationItem
     */
    public function execute(string $tenantId, string $reservationCode, int $itemId): ReservationItem
    {
        return $this->executeInTenantContext($tenantId, function () use ($reservationCode, $itemId) {
            $reservation = Reservation::where('code', $reservationCode)->firstOrFail();
            $item = $reservation->items()->where('id', $itemId)->firstOrFail();

            // Cancel the item
            $item->update(['status' => ReservationItem::STATUS_CANCELLED]);

            // Recalculate reservation totals
            $reservation->recalculateTotals();
            $reservation->save();

            return $item->fresh();
        });
    }
}
