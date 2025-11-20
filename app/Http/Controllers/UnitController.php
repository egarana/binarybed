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
        protected UnitService $unitService,
        protected TenantService $tenantService
    ) {}

    public function index(Request $request): Response
    {
        $units = $this->unitService->getAllPaginated($request);

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

    // public function edit(Unit $unit): Response
    // {
    //     $unit = $this->unitService->getForEdit($unit);
        
    //     return Inertia::render('units/Edit', compact('unit'));
    // }

    // public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    // {
    //     $this->unitService->update($unit, $request->validated());

    //     return redirect()->route('units.index', ['sort' => '-updated_at']);
    // }

    // public function destroy(Unit $unit): RedirectResponse
    // {
    //     $this->unitService->delete($unit);

    //     return redirect()->route('units.index');
    // }
}
