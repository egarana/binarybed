<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
        protected TenantService $tenantService
    ) {}

    public function index(Request $request): Response
    {
        $reservations = $this->reservationService->getAllFromAllTenantsPaginated($request);

        return Inertia::render('reservations/Index', compact('reservations'));
    }

    public function create(Request $request): Response
    {
        $tenants = $this->tenantService->search($request->input('search'));
        $statuses = Reservation::STATUSES;

        return Inertia::render('reservations/Create', compact('tenants', 'statuses'));
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $this->reservationService->create($request->validated());

        return redirect()->route('reservations.index', ['sort' => '-created_at']);
    }

    public function edit(string $tenant, string $code, Request $request): Response
    {
        $reservation = $this->reservationService->getForEdit($tenant, $code);
        $statuses = Reservation::STATUSES;

        return Inertia::render('reservations/Edit', compact('reservation', 'statuses'));
    }

    public function update(UpdateReservationRequest $request, string $tenantId, string $code): RedirectResponse
    {
        $this->reservationService->update($tenantId, $code, $request->validated());

        return redirect()->route('reservations.index', ['sort' => '-updated_at']);
    }

    // Note: No destroy method - reservations cannot be deleted, only cancelled via status change
}
