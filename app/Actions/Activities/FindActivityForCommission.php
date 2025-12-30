<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;

class FindActivityForCommission
{
    use HandlesTenancy;

    /**
     * Find an activity by tenant ID and slug with commission config.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function execute(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $activity = Activity::where('slug', $slug)->with('commissionConfig')->firstOrFail();

            return [
                'id' => $activity->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $activity->name,
                'slug' => $activity->slug,
                'commission_config' => $activity->commissionConfig ? [
                    'commission_type' => $activity->commissionConfig->commission_type,
                    'commission_percentage' => $activity->commissionConfig->commission_percentage,
                    'commission_fixed' => $activity->commissionConfig->commission_fixed,
                    'currency' => $activity->commissionConfig->currency ?? 'IDR',
                ] : null,
            ];
        });
    }
}
