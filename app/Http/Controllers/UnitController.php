<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUserToUnitRequest;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
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
        protected UserService $userService
    ) {}

    public function index(Request $request): Response
    {
        $units = $this->unitService->getAllFromAllTenantsPaginated($request);

        return Inertia::render('units/Index', compact('units'));
    }

    public function create(Request $request): Response
    {
        $tenants = $this->tenantService->search($request->input('search'));

        return Inertia::render('units/Create', compact('tenants'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->unitService->create($request->validated());

        return redirect()->route('units.index', ['sort' => '-created_at']);
    }

    public function edit(string $tenantId, string $slug): Response
    {
        $unit = $this->unitService->getForEdit($tenantId, $slug);

        return Inertia::render('units/Edit', compact('unit'));
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

        return Inertia::render('units/users/Index', compact('unit', 'users'));
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
}
