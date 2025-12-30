<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Services\UserSyncService;

class UpdateUserUnitRole
{
    use HandlesTenancy;

    /**
     * Update a user's assignment for a specific unit.
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
            // Find unit by slug in tenant database
            $unit = Unit::where('slug', $slug)->firstOrFail();

            // Update user's assignment in the pivot table
            UserSyncService::updateUnitUserRole(
                centralUserId: $userGlobalId,
                unit: $unit,
                data: $data
            );
        });
    }
}
