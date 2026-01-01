<?php

namespace App\Http\Controllers;

use App\Actions\Rates\DeleteRateFromResource;
use App\Actions\Rates\GetRatesForResource;
use App\Actions\Units\UpdateUnitCommission;
use App\Http\Requests\AttachUserToUnitRequest;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateCommissionConfigRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Requests\UpdateUserAssignmentRequest;
use App\Models\Unit;
use App\Services\FeatureService;
use App\Services\TenantService;
use App\Services\UnitService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function __construct(
        protected UnitService $unitService,
        protected TenantService $tenantService,
        protected UserService $userService,
        protected FeatureService $featureService,
        protected GetRatesForResource $getRatesForResource,
        protected DeleteRateFromResource $deleteRateFromResource,
        protected UpdateUnitCommission $updateUnitCommission
    ) {}

    public function index(Request $request): Response
    {
        $units = $this->unitService->getAllFromAllTenantsPaginated($request);

        return Inertia::render('units/Index', compact('units'));
    }

    public function create(Request $request): Response
    {
        $tenants = $this->tenantService->search($request->input('search'));
        // Get features for SearchableSelect (with search support)
        $features = $this->featureService->search($request->input('search'), 5);

        return Inertia::render('units/Create', compact('tenants', 'features'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->unitService->create($request->validated());

        return redirect()->route('units.index', ['sort' => '-created_at']);
    }

    public function edit(string $tenant, string $slug, Request $request): Response
    {
        $unit = $this->unitService->getForEdit($tenant, $slug);

        // Get features for SearchableSelect (with search support)
        $features = $this->featureService->search(
            $request->input('search'),
            5
        );

        return Inertia::render('units/Edit', compact('unit', 'features'));
    }

    public function update(UpdateUnitRequest $request, string $tenantId, string $slug): RedirectResponse
    {
        $this->unitService->update($tenantId, $slug, $request->validated());

        return redirect()->route('units.index', ['sort' => '-updated_at']);
    }

    public function destroy(string $tenantId, string $slug): RedirectResponse
    {
        $this->unitService->delete($tenantId, $slug);

        return redirect()->route('units.index');
    }

    public function users(Request $request, string $tenantId, string $slug): Response
    {
        $unit = $this->unitService->getForEdit($tenantId, $slug);
        $users = $this->userService->search($request->input('search'));
        $attachedUsers = $this->unitService->getAttachedUsers($tenantId, $slug);

        // Calculate total commission split for remaining allocation display
        $totalCommissionSplit = $attachedUsers->sum('commission_split');

        return Inertia::render('units/users/Index', compact('unit', 'users', 'attachedUsers', 'totalCommissionSplit'));
    }

    public function attachUser(AttachUserToUnitRequest $request, string $tenantId, string $slug): RedirectResponse
    {
        $this->unitService->attachUserToUnit(
            $tenantId,
            $slug,
            $request->validated()
        );

        return redirect()->route('units.users', [$tenantId, $slug]);
    }

    public function detachUser(string $tenantId, string $slug, string $userGlobalId): RedirectResponse
    {
        $this->unitService->detachUserFromUnit($tenantId, $slug, $userGlobalId);

        return redirect()->route('units.users', [$tenantId, $slug]);
    }

    public function updateUser(
        UpdateUserAssignmentRequest $request,
        string $tenantId,
        string $slug,
        string $userGlobalId
    ): RedirectResponse {
        $this->unitService->updateUserUnitRole(
            $tenantId,
            $slug,
            $userGlobalId,
            $request->validated()
        );

        return redirect()->route('units.users', [$tenantId, $slug]);
    }

    public function rates(Request $request, string $tenantId, string $slug): Response
    {
        $unit = $this->unitService->getForEdit($tenantId, $slug);
        $rates = $this->getRatesForResource->execute(
            $tenantId,
            Unit::class,
            $unit['id']
        );

        return Inertia::render('units/rates/Index', compact('unit', 'rates'));
    }

    public function createRate(string $tenantId, string $slug): Response
    {
        $unit = $this->unitService->getForEdit($tenantId, $slug);

        // Format product_display like Edit page
        $productDisplay = $unit['name'] . ' - ' . $unit['tenant_name'] . ' (Unit)';

        return Inertia::render('units/rates/Create', [
            'unit' => $unit,
            'productDisplay' => $productDisplay,
        ]);
    }

    public function deleteRate(string $tenantId, string $slug, int $rateId): RedirectResponse
    {
        $this->deleteRateFromResource->execute($tenantId, $rateId);

        return redirect()->route('units.rates', [$tenantId, $slug]);
    }

    public function commission(string $tenantId, string $slug): Response
    {
        $unit = $this->unitService->getForCommission($tenantId, $slug);

        return Inertia::render('units/Commission', [
            'unit' => $unit,
        ]);
    }

    public function updateCommission(
        UpdateCommissionConfigRequest $request,
        string $tenantId,
        string $slug
    ): RedirectResponse {
        $this->updateUnitCommission->execute($tenantId, $slug, $request->validated());

        return redirect()->route('units.index', ['sort' => '-updated_at']);
    }
}
