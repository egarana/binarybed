<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $service
    ) {}
    
    public function index(Request $request): Response
    {
        $tenants = $this->service->getAllPaginated($request);

        return Inertia::render('tenants/Index', compact('tenants'));
    }

    public function create(): Response
    {
        return Inertia::render('tenants/Create');
    }

    public function store(StoreTenantRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('tenants.index', ['sort' => '-created_at']);
    }

    public function edit(Tenant $tenant): Response
    {
        $tenant = $tenant->load('domains');

        return Inertia::render('tenants/Edit', compact('tenant'));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $this->service->update($tenant, $request->validated());

        return redirect()->route('tenants.index', ['sort' => '-updated_at']);
    }

    public function destroy(Tenant $tenant)
    {
        $this->service->delete($tenant);

        return redirect()->route('tenants.index');
    }
}
