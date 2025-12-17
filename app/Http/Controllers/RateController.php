<?php

namespace App\Http\Controllers;

use App\Actions\Rates\GetAllResourcesWithTenantInfo;
use App\Http\Requests\StoreRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Services\RateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RateController extends Controller
{
    public function __construct(
        protected RateService $rateService,
        protected GetAllResourcesWithTenantInfo $getAllResourcesWithTenantInfo
    ) {}

    public function index(Request $request): Response
    {
        $rates = $this->rateService->getAllFromAllTenantsPaginated($request);

        return Inertia::render('rates/Index', compact('rates'));
    }

    public function create(Request $request): Response
    {
        // Only fetch resources when search input exists (no initial load)
        $search = $request->input('search');
        $resources = $search
            ? $this->getAllResourcesWithTenantInfo->execute($search)
            : [];

        return Inertia::render('rates/Create', compact('resources'));
    }

    public function store(StoreRateRequest $request): RedirectResponse
    {
        $result = $this->rateService->create($request->validated());
        $rate = $result['rate'];
        $resourceSlug = $result['resource_slug'];

        // Only redirect to resource rates page if explicitly requested (from resource page)
        $redirectToResource = $request->input('redirect_to_resource') === '1';

        if ($redirectToResource && $resourceSlug) {
            $validated = $request->validated();
            $resourceType = class_basename($validated['rateable_type']);
            $tenantId = $validated['tenant_id'];

            if ($resourceType === 'Unit') {
                return redirect()->route('units.rates', [$tenantId, $resourceSlug]);
            } elseif ($resourceType === 'Activity') {
                return redirect()->route('activities.rates', [$tenantId, $resourceSlug]);
            }
        }

        return redirect()->route('rates.index', ['sort' => '-created_at']);
    }

    public function edit(string $tenant, string $resource, string $slug, Request $request): Response
    {
        $rate = $this->rateService->getForEdit($tenant, $slug);

        return Inertia::render('rates/Edit', compact('rate'));
    }

    public function update(UpdateRateRequest $request, string $tenantId, string $resource, string $slug): RedirectResponse
    {
        $this->rateService->update($tenantId, $slug, $request->validated());

        return redirect()->route('rates.index', ['sort' => '-updated_at']);
    }

    public function destroy(string $tenantId, string $resource, string $slug): RedirectResponse
    {
        $this->rateService->delete($tenantId, $slug);

        return redirect()->route('rates.index');
    }
}
