<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;

class DetachUserFromUnit
{
    use HandlesTenancy;

    /**
     * Detach a user from a unit.
     *
     * @param string $tenantId
     * @param string $slug
     * @param string $userGlobalId
     * @return void
     */
    public function execute(string $tenantId, string $slug, string $userGlobalId): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug, $userGlobalId) {
            // Find unit by slug in tenant database
            $unit = Unit::where('slug', $slug)->firstOrFail();

            // Detach user from unit using global_id
            // The users() relation uses global_id as the relatedPivotKey
            $unit->users()->detach($userGlobalId);

            // Touch unit to update updated_at timestamp
            $unit->touch();
        });
    }
}
