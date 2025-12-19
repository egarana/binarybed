<?php

namespace App\Services;

use App\Actions\Reservations\CreateReservation;
use App\Actions\Reservations\FindReservationByTenantAndCode;
use App\Actions\Reservations\UpdateReservation;
use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use Illuminate\Http\Request;

class ReservationService
{
    public function __construct(
        protected ReservationRepository $reservationRepository,
        protected CreateReservation $createReservation,
        protected UpdateReservation $updateReservation,
        protected FindReservationByTenantAndCode $findReservationByTenantAndCode
    ) {}

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        return $this->reservationRepository->getAllFromAllTenantsPaginated($request);
    }

    public function getForEdit(string $tenantId, string $code): array
    {
        return $this->findReservationByTenantAndCode->execute($tenantId, $code);
    }

    public function create(array $data): Reservation
    {
        return $this->createReservation->execute($data);
    }

    public function update(string $tenantId, string $code, array $data): Reservation
    {
        return $this->updateReservation->execute($tenantId, $code, $data);
    }
}
