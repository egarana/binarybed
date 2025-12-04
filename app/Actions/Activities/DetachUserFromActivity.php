<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;

class DetachUserFromActivity
{
    use HandlesTenancy;

    /**
     * Detach a user from a activity.
     *
     * @param string $tenantId
     * @param string $slug
     * @param string $userGlobalId
     * @return void
     */
    public function execute(string $tenantId, string $slug, string $userGlobalId): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug, $userGlobalId) {
            // Find activity by slug in tenant database
            $activity = Activity::where('slug', $slug)->firstOrFail();

            // Detach user from activity using global_id
            // The users() relation uses global_id as the relatedPivotKey
            $activity->users()->detach($userGlobalId);
        });
    }
}
