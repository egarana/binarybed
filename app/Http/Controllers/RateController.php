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
        $rate = $this->rateService->getForEdit($tenant, $resource, $slug);

        // Capture return from query param (passed from units/rates or activities/rates page)
        $returnTo = $request->query('return', '0');

        return Inertia::render('rates/Edit', compact('rate', 'returnTo'));
    }

    public function update(UpdateRateRequest $request, string $tenantId, string $resource, string $slug): RedirectResponse
    {
        $rate = $this->rateService->update($tenantId, $resource, $slug, $request->validated());

        // Redirect to resource rates page if requested
        $return = $request->input('return') === '1';

        if ($return) {
            $rateableType = class_basename($rate->rateable_type);

            if ($rateableType === 'Unit') {
                return redirect()->route('units.rates', [$tenantId, $resource, 'sort' => '-updated_at']);
            } elseif ($rateableType === 'Activity') {
                return redirect()->route('activities.rates', [$tenantId, $resource, 'sort' => '-updated_at']);
            }
        }

        return redirect()->route('rates.index', ['sort' => '-updated_at']);
    }

    public function destroy(string $tenantId, string $resource, string $slug): RedirectResponse
    {
        $this->rateService->delete($tenantId, $resource, $slug);

        return redirect()->route('rates.index');
    }
}
