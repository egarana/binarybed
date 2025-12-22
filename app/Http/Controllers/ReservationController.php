<?php

namespace App\Http\Controllers;

use App\Actions\Reservations\AddReservationItem;
use App\Actions\Reservations\CancelReservationItem;
use App\Actions\Reservations\GetItemsForReservation;
use App\HandlesTenancy;
use App\Http\Requests\StoreReservationItemRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Services\ReservationService;
use App\Services\TenantService;
use App\Services\UnitService;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    use HandlesTenancy;

    public function __construct(
        protected ReservationService $reservationService,
        protected TenantService $tenantService,
        protected GetItemsForReservation $getItemsForReservation,
        protected AddReservationItem $addReservationItem,
        protected CancelReservationItem $cancelReservationItem,
        protected UnitService $unitService,
        protected ActivityService $activityService
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

    /*
    |--------------------------------------------------------------------------
    | Reservation Items Management
    |--------------------------------------------------------------------------
    */

    /**
     * Display list of items for a reservation.
     */
    public function items(Request $request, string $tenantId, string $code): Response
    {
        $reservation = $this->reservationService->getForEdit($tenantId, $code);
        $items = $this->getItemsForReservation->execute($tenantId, $code);

        return Inertia::render('reservations/items/Index', compact('reservation', 'items'));
    }

    /**
     * Show form to add a new item to reservation.
     */
    public function createItem(Request $request, string $tenantId, string $code): Response
    {
        $reservation = $this->reservationService->getForEdit($tenantId, $code);

        // Get products for this tenant
        $products = $this->getResourcesForTenant($tenantId, $request->input('search'));

        // Get pricing types for dropdown
        $pricingTypes = [
            ['value' => ReservationItem::PRICING_PER_NIGHT, 'label' => 'Per Night'],
            ['value' => ReservationItem::PRICING_PER_PERSON, 'label' => 'Per Person'],
            ['value' => ReservationItem::PRICING_PER_HOUR, 'label' => 'Per Hour'],
            ['value' => ReservationItem::PRICING_FLAT, 'label' => 'Flat Rate'],
        ];

        return Inertia::render('reservations/items/Create', compact('reservation', 'products', 'pricingTypes'));
    }

    /**
     * Store a new item in the reservation.
     */
    public function storeItem(StoreReservationItemRequest $request, string $tenantId, string $code): RedirectResponse
    {
        $this->addReservationItem->execute($tenantId, $code, $request->validated());

        return redirect()->route('reservations.items', [$tenantId, $code, 'sort' => '-created_at']);
    }

    /**
     * Cancel an item from the reservation (soft delete via status).
     */
    public function cancelItem(string $tenantId, string $code, int $itemId): RedirectResponse
    {
        $this->cancelReservationItem->execute($tenantId, $code, $itemId);

        return redirect()->route('reservations.items', [$tenantId, $code]);
    }

    /**
     * API endpoint to get rates for a specific resource.
     */
    public function getResourceRates(Request $request, string $tenantId, string $code): array
    {
        $resourceType = $request->input('resource_type');
        $resourceId = $request->input('resource_id');

        if (!$resourceType || !$resourceId) {
            return ['rates' => []];
        }

        return $this->executeInTenantContext($tenantId, function () use ($resourceType, $resourceId) {
            $rates = Rate::where('rateable_type', $resourceType)
                ->where('rateable_id', $resourceId)
                ->where('is_active', true)
                ->get()
                ->map(fn($rate) => [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'price' => $rate->price,
                    'currency' => $rate->currency,
                    'pricing_type' => $rate->pricing_type ?? ReservationItem::PRICING_PER_NIGHT,
                    'description' => $rate->description,
                ]);

            return ['rates' => $rates];
        });
    }

    /**
     * Get all resources (units + activities) for a tenant.
     */
    private function getResourcesForTenant(string $tenantId, ?string $search = null): array
    {
        return $this->executeInTenantContext($tenantId, function () use ($search) {
            $resources = [];

            // Get Units
            $unitsQuery = \App\Models\Unit::query()->withCount(['rates' => function ($query) {
                $query->where('is_active', true);
            }]);
            if ($search) {
                $unitsQuery->where('name', 'like', "%{$search}%");
            }
            $units = $unitsQuery->limit(20)->get();

            foreach ($units as $unit) {
                $resources[] = [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'type' => 'App\\Models\\Unit',
                    'type_label' => 'Unit',
                    'description' => $unit->description ?? null,
                    'rates_count' => $unit->rates_count,
                ];
            }

            // Get Activities
            $activitiesQuery = \App\Models\Activity::query()->withCount(['rates' => function ($query) {
                $query->where('is_active', true);
            }]);
            if ($search) {
                $activitiesQuery->where('name', 'like', "%{$search}%");
            }
            $activities = $activitiesQuery->limit(20)->get();

            foreach ($activities as $activity) {
                $resources[] = [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'type' => 'App\\Models\\Activity',
                    'type_label' => 'Activity',
                    'description' => $activity->description ?? null,
                    'rates_count' => $activity->rates_count,
                ];
            }

            return $resources;
        });
    }
}
