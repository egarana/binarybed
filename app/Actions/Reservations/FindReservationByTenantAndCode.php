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
        return $this->executeInTenantContext($tenantId, function () use ($tenantId, $code) {
            $reservation = Reservation::where('code', $code)->firstOrFail();

            return [
                'id' => $reservation->id,
                'code' => $reservation->code,
                'guest_name' => $reservation->guest_name,
                'status' => $reservation->status,
                'tenant_id' => $tenantId,
                'created_at' => $reservation->created_at,
                'updated_at' => $reservation->updated_at,
            ];
        });
    }
}
