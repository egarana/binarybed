<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Repositories\ActivityRepository;
use Illuminate\Http\Request;

class GetAttachedUsersForActivity
{
    use HandlesTenancy;

    public function __construct(
        protected ActivityRepository $activityRepository,
        protected Request $request
    ) {}

    /**
     * Get all users attached to a activity with filtering and sorting support.
     *
     * @param string $tenantId
     * @param string $slug
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(string $tenantId, string $slug): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->executeInTenantContext($tenantId, function () use ($slug) {
            $activity = Activity::where('slug', $slug)->firstOrFail();

            return $this->activityRepository->getAttachedUsersPaginated($activity, $this->request);
        });
    }
}
