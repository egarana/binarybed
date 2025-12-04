<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUserToActivityRequest;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Requests\UpdateUserAssignmentRequest;
use App\Models\Activity;
use App\Services\FeatureService;
use App\Services\TenantService;
use App\Services\ActivityService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activityService,
        protected TenantService $tenantService,
        protected UserService $userService,
        protected FeatureService $featureService
    ) {}

    public function index(Request $request): Response
    {
        $activities = $this->activityService->getAllFromAllTenantsPaginated($request);

        return Inertia::render('activities/Index', compact('activities'));
    }

    public function create(Request $request): Response
    {
        $tenants = $this->tenantService->search($request->input('search'));
        // Get features for SearchableSelect (with search support)
        $features = $this->featureService->search($request->input('search'));

        return Inertia::render('activities/Create', compact('tenants', 'features'));
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $this->activityService->create($request->validated());

        return redirect()->route('activities.index', ['sort' => '-created_at']);
    }

    public function edit(string $tenant, string $slug, Request $request): Response
    {
        $activity = $this->activityService->getForEdit($tenant, $slug);

        // Get features for SearchableSelect (with search support)
        $features = $this->featureService->search(
            $request->input('search'),
            100
        );

        return Inertia::render('activities/Edit', compact('activity', 'features'));
    }

    public function update(UpdateActivityRequest $request, string $tenantId, string $slug): RedirectResponse
    {
        $this->activityService->update($tenantId, $slug, $request->validated());

        return redirect()->route('activities.index', ['sort' => '-updated_at']);
    }

    public function destroy(string $tenantId, string $slug): RedirectResponse
    {
        $this->activityService->delete($tenantId, $slug);

        return redirect()->route('activities.index');
    }

    public function users(Request $request, string $tenantId, string $slug): Response
    {
        $activity = $this->activityService->getForEdit($tenantId, $slug);
        $users = $this->userService->search($request->input('search'));
        $attachedUsers = $this->activityService->getAttachedUsers($tenantId, $slug);

        return Inertia::render('activities/users/Index', compact('activity', 'users', 'attachedUsers'));
    }

    public function attachUser(AttachUserToActivityRequest $request, string $tenantId, string $slug): RedirectResponse
    {
        $this->activityService->attachUserToActivity(
            $tenantId,
            $slug,
            $request->validated()
        );

        return redirect()->route('activities.users', [$tenantId, $slug]);
    }

    public function detachUser(string $tenantId, string $slug, string $userGlobalId): RedirectResponse
    {
        $this->activityService->detachUserFromActivity($tenantId, $slug, $userGlobalId);

        return redirect()->route('activities.users', [$tenantId, $slug]);
    }

    public function updateUser(
        UpdateUserAssignmentRequest $request,
        string $tenantId,
        string $slug,
        string $userGlobalId
    ): RedirectResponse {
        $this->activityService->updateUserActivityRole(
            $tenantId,
            $slug,
            $userGlobalId,
            $request->validated()
        );

        return redirect()->route('activities.users', [$tenantId, $slug]);
    }
}
