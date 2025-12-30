<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;

class UpdateActivityCommission
{
    use HandlesTenancy;

    /**
     * Update commission config for an activity.
     *
     * @param string $tenantId
     * @param string $slug
     * @param array $data
     * @return Activity
     */
    public function execute(string $tenantId, string $slug, array $data): Activity
    {
        return $this->executeInTenantContext($tenantId, function () use ($slug, $data) {
            $activity = Activity::where('slug', $slug)->firstOrFail();

            if (!empty($data['commission_type'])) {
                $activity->commissionConfig()->updateOrCreate(
                    ['resourceable_type' => get_class($activity), 'resourceable_id' => $activity->id],
                    [
                        'commission_type' => $data['commission_type'],
                        'commission_percentage' => $data['commission_type'] === 'percentage' ? $data['commission_percentage'] : null,
                        'commission_fixed' => $data['commission_type'] === 'fixed' ? ($data['commission_fixed'] ?? 0) : null,
                        'currency' => $data['commission_type'] === 'fixed' ? ($data['currency'] ?? 'IDR') : null,
                        'is_active' => true,
                    ]
                );
            } else {
                // If commission_type is empty/null, delete existing config
                $activity->commissionConfig()?->delete();
            }

            // Touch the activity to update its updated_at timestamp
            $activity->touch();

            return $activity->fresh(['commissionConfig']);
        });
    }
}
