<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Services\UserSyncService;

class AttachUserToActivity
{
    use HandlesTenancy;

    /**
     * Attach a user to an activity with automatic sync from central database.
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

            // Build pivot data
            $pivotData = ['role' => $data['role']];
            if (isset($data['commission_split'])) {
                $pivotData['commission_split'] = $data['commission_split'];
            }

            // Sync user dari central ke tenant, lalu attach ke activity
            UserSyncService::attachActivityToUser(
                centralUserId: $data['user_id'],
                activity: $activity,
                pivotData: $pivotData
            );
        });
    }
}
