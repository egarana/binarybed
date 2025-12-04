<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Repositories\ActivityRepository;

class DeleteActivity
{
    use HandlesTenancy;

    public function __construct(
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Delete a activity in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $slug
     * @return void
     */
    public function execute(string $tenantId, string $slug): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug) {
            $activity = Activity::where('slug', $slug)->firstOrFail();

            $this->activityRepository->delete($activity);
        });
    }
}
