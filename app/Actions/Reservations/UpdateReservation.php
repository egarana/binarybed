<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use App\HandlesTenancy;

class UpdateReservation
{
    use HandlesTenancy;

    public function __construct(
        protected ReservationRepository $reservationRepository
    ) {}

    /**
     * Update a reservation in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $code
     * @param array $data
     * @return Reservation
     */
    public function execute(string $tenantId, string $code, array $data): Reservation
    {
        return $this->executeInTenantContext($tenantId, function () use ($code, $data) {
            $reservation = Reservation::where('code', $code)->firstOrFail();

            // Code cannot be changed
            unset($data['code']);

            return $this->reservationRepository->update($reservation, $data);
        });
    }
}
