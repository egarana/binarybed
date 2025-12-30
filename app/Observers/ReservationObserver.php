<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\SettlementService;

class ReservationObserver
{
    public function __construct(
        protected SettlementService $settlementService
    ) {}

    /**
     * Handle the Reservation "updated" event.
     * Triggers settlement processing when status changes to COMPLETED.
     */
    public function updated(Reservation $reservation): void
    {
        // Only trigger when status changed
        if (!$reservation->wasChanged('status')) {
            return;
        }

        // Only process when status is COMPLETED
        if ($reservation->status !== Reservation::STATUS_COMPLETED) {
            return;
        }

        // Process settlement (idempotency handled inside service)
        $this->settlementService->processReservation($reservation);
    }
}
