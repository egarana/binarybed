<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\HandlesTenancy;

class FindReservationByTenantAndCode
{
    use HandlesTenancy;

    /**
     * Find a reservation by tenant and code for editing.
     *
     * @param string $tenantId
     * @param string $code
     * @return array
     */
    public function execute(string $tenantId, string $code): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($tenantId, $code) {
            $reservation = Reservation::where('code', $code)->firstOrFail();

            return [
                'id' => $reservation->id,
                'code' => $reservation->code,
                'guest_name' => $reservation->guest_name,
                'guest_email' => $reservation->guest_email,
                'guest_phone' => $reservation->guest_phone,
                'status' => $reservation->status,
                'subtotal' => $reservation->subtotal,
                'total_amount' => $reservation->total_amount,
                'currency' => $reservation->currency,
                'notes' => $reservation->notes,
                'cancellation_reason' => $reservation->cancellation_reason,
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant->name,
                'created_at' => $reservation->created_at,
                'updated_at' => $reservation->updated_at,
            ];
        });
    }
}
