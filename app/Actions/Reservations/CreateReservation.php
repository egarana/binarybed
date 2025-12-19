<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use App\HandlesTenancy;

class CreateReservation
{
    use HandlesTenancy;

    public function __construct(
        protected ReservationRepository $reservationRepository
    ) {}

    /**
     * Create a new reservation in the specified tenant's database.
     *
     * @param array $data
     * @return Reservation
     */
    public function execute(array $data): Reservation
    {
        $tenantId = $data['tenant_id'];

        return $this->executeInTenantContext($tenantId, function () use ($data) {
            // Generate unique code
            $data['code'] = Reservation::generateUniqueCode();

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = Reservation::STATUS_PENDING;
            }

            // Set default currency if not provided
            if (!isset($data['currency'])) {
                $data['currency'] = 'IDR';
            }

            return $this->reservationRepository->create($data);
        });
    }
}
