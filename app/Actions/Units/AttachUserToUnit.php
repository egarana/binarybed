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

            // Sync user dari central ke tenant, lalu attach ke unit
            // UserSyncService akan handle:
            // 1. Fetch user dari central database
            // 2. Create/update user di tenant database
            // 3. Attach user ke unit via resource_users table
            UserSyncService::attachUnitToUser(
                centralUserId: $data['user_id'],
                unit: $unit,
                pivotData: ['role' => $data['role']]
            );
        });
    }
}
