<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Services\RateService;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RateController extends Controller
{
    public function __construct(
        protected RateService $rateService,
        protected TenantService $tenantService
    ) {}

    public function index(Request $request): Response
    {
        $rates = $this->rateService->getAllFromAllTenantsPaginated($request);

        return Inertia::render('rates/Index', compact('rates'));
    }

    public function create(Request $request): Response
    {
        $tenants = $this->tenantService->search($request->input('search'));

        return Inertia::render('rates/Create', compact('tenants'));
    }

    public function store(StoreRateRequest $request): RedirectResponse
    {
        $this->rateService->create($request->validated());

        return redirect()->route('rates.index', ['sort' => '-created_at']);
    }

    public function edit(string $tenant, string $slug, Request $request): Response
    {
        $rate = $this->rateService->getForEdit($tenant, $slug);

        return Inertia::render('rates/Edit', compact('rate'));
    }

    public function update(UpdateRateRequest $request, string $tenantId, string $slug): RedirectResponse
    {
        $this->rateService->update($tenantId, $slug, $request->validated());

        return redirect()->route('rates.index', ['sort' => '-updated_at']);
    }

    public function destroy(string $tenantId, string $slug): RedirectResponse
    {
        $this->rateService->delete($tenantId, $slug);

        return redirect()->route('rates.index');
    }
}
