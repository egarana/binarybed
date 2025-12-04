<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Services\UserSyncService;

class AttachUserToActivity
{
    use HandlesTenancy;

    /**
     * Attach a user to a activity with automatic sync from central database.
     *
     * @param string $tenantId
     * @param string $slug
     * @param array $data
     * @return void
     */
    public function execute(string $tenantId, string $slug, array $data): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug, $data) {
            // Find activity by slug in tenant database
            $activity = Activity::where('slug', $slug)->firstOrFail();

            // Sync user dari central ke tenant, lalu attach ke activity
            // UserSyncService akan handle:
            // 1. Fetch user dari central database
            // 2. Create/update user di tenant database
            // 3. Attach user ke activity via resource_users table
            UserSyncService::attachActivityToUser(
                centralUserId: $data['user_id'],
                activity: $activity,
                pivotData: ['role' => $data['role']]
            );
        });
    }
}
