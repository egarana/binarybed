<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Tenant;
use App\Services\TenantService;
use App\Repositories\TenantRepository;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;

class TenantController extends Controller
{
    public function index(TenantRepository $repository): Response
    {
        return Inertia::render('tenants/Index', [
            'tenants' => $repository->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('tenants/Create');
    }

    public function store(StoreTenantRequest $request, TenantService $service)
    {
        $service->create($request->validated());

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('tenants/Edit', [
            'tenant' => $tenant->load('domains'),
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant, TenantService $service)
    {
        $service->update($tenant, $request->validated());

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant, TenantService $service)
    {
        $service->delete($tenant);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }
}
