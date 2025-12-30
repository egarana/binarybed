<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Services\UserSyncService;

class UpdateUserActivityRole
{
    use HandlesTenancy;

    /**
     * Update a user's assignment for a specific activity.
     *
     * @param string $tenantId
     * @param string $slug
     * @param string $userGlobalId
     * @param array $data
     * @return void
     */
    public function execute(string $tenantId, string $slug, string $userGlobalId, array $data): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug, $userGlobalId, $data) {
            // Find activity by slug in tenant database
            $activity = Activity::where('slug', $slug)->firstOrFail();

            // Update user's assignment in the pivot table
            UserSyncService::updateActivityUserRole(
                centralUserId: $userGlobalId,
                activity: $activity,
                data: $data
            );
        });
    }
}
