<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use Illuminate\Validation\ValidationException;

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
     * @throws ValidationException
     */
    public function execute(string $tenantId, string $slug, string $userGlobalId): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug, $userGlobalId) {
            // Find unit by slug in tenant database
            $unit = Unit::where('slug', $slug)->firstOrFail();

            // Check if user is protected (platform owner)
            $pivotRecord = $unit->users()->wherePivot('global_id', $userGlobalId)->first();
            if ($pivotRecord && $pivotRecord->pivot->is_protected) {
                throw ValidationException::withMessages([
                    'user' => ['Platform owner cannot be removed from this resource.'],
                ]);
            }

            // Detach user from unit using global_id
            // The users() relation uses global_id as the relatedPivotKey
            $unit->users()->detach($userGlobalId);

            // Touch unit to update updated_at timestamp
            $unit->touch();
        });
    }
}
