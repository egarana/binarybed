<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Tenant;
use App\Services\TenantService;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $service
    ) {}

    public function index(Request $request): Response
    {
        $tenants = $this->service->getPaginated($request);

        return Inertia::render('tenants/Index', compact('tenants'));
    }

    public function create(): Response
    {
        return Inertia::render('tenants/Create');
    }

    public function store(StoreTenantRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('tenants.index');
    }

    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('tenants/Edit', [
            'tenant' => $tenant->load('domains'),
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $this->service->update($tenant, $request->validated());

        return redirect()->route('tenants.index');
    }

    public function destroy(Tenant $tenant)
    {
        $this->service->delete($tenant);

        return redirect()->route('tenants.index');
    }
}
