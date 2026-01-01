<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use Illuminate\Validation\ValidationException;

class DetachUserFromActivity
{
    use HandlesTenancy;

    /**
     * Detach a user from an activity.
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
            // Find activity by slug in tenant database
            $activity = Activity::where('slug', $slug)->firstOrFail();

            // Check if user is protected (platform owner)
            $pivotRecord = $activity->users()->wherePivot('global_id', $userGlobalId)->first();
            if ($pivotRecord && $pivotRecord->pivot->is_protected) {
                throw ValidationException::withMessages([
                    'user' => ['Platform owner cannot be removed from this resource.'],
                ]);
            }

            // Detach user from activity using global_id
            // The users() relation uses global_id as the relatedPivotKey
            $activity->users()->detach($userGlobalId);

            // Touch activity to update updated_at timestamp
            $activity->touch();
        });
    }
}
