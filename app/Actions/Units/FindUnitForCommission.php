<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;

class FindUnitForCommission
{
    use HandlesTenancy;

    /**
     * Find a unit by tenant ID and slug with commission config.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function execute(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $unit = Unit::where('slug', $slug)->with('commissionConfig')->firstOrFail();

            return [
                'id' => $unit->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $unit->name,
                'slug' => $unit->slug,
                'commission_config' => $unit->commissionConfig ? [
                    'commission_type' => $unit->commissionConfig->commission_type,
                    'commission_percentage' => $unit->commissionConfig->commission_percentage,
                    'commission_fixed' => $unit->commissionConfig->commission_fixed,
                    'currency' => $unit->commissionConfig->currency ?? 'IDR',
                ] : null,
            ];
        });
    }
}
