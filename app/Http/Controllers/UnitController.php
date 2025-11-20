<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\TenantService;
use App\Services\UnitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function __construct(
        protected UnitService $service,
        protected TenantService $tenantService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('units/Index');
    }

    public function create(Request $request): Response
    {
        $tenants = $this->tenantService->search($request->input('search'));

        return Inertia::render('units/Create', compact('tenants'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('units.index', ['sort' => '-created_at']);
    }

    // public function edit(Unit $unit): Response
    // {
    //     $unit = $this->service->getForEdit($unit);
        
    //     return Inertia::render('units/Edit', compact('unit'));
    // }

    // public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    // {
    //     $this->service->update($unit, $request->validated());

    //     return redirect()->route('units.index', ['sort' => '-updated_at']);
    // }

    // public function destroy(Unit $unit): RedirectResponse
    // {
    //     $this->service->delete($unit);

    //     return redirect()->route('units.index');
    // }
}
