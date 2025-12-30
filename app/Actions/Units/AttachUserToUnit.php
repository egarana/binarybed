<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Services\UserSyncService;

class AttachUserToUnit
{
    use HandlesTenancy;

    /**
     * Attach a user to a unit with automatic sync from central database.
     *
     * @param string $tenantId
     * @param string $slug
     * @param array $data
     * @return void
     */
    public function execute(string $tenantId, string $slug, array $data): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug, $data) {
            // Find unit by slug in tenant database
            $unit = Unit::where('slug', $slug)->firstOrFail();

            // Build pivot data
            $pivotData = ['role' => $data['role']];
            if (isset($data['commission_split'])) {
                $pivotData['commission_split'] = $data['commission_split'];
            }

            // Sync user dari central ke tenant, lalu attach ke unit
            UserSyncService::attachUnitToUser(
                centralUserId: $data['user_id'],
                unit: $unit,
                pivotData: $pivotData
            );
        });
    }
}
