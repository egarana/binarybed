<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;

class UpdateUnitCommission
{
    use HandlesTenancy;

    /**
     * Update commission config for a unit.
     *
     * @param string $tenantId
     * @param string $slug
     * @param array $data
     * @return Unit
     */
    public function execute(string $tenantId, string $slug, array $data): Unit
    {
        return $this->executeInTenantContext($tenantId, function () use ($slug, $data) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            if (!empty($data['commission_type'])) {
                $unit->commissionConfig()->updateOrCreate(
                    ['resourceable_type' => get_class($unit), 'resourceable_id' => $unit->id],
                    [
                        'commission_type' => $data['commission_type'],
                        'commission_percentage' => $data['commission_type'] === 'percentage' ? $data['commission_percentage'] : null,
                        'commission_fixed' => $data['commission_type'] === 'fixed' ? $data['commission_fixed'] : null,
                        'currency' => $data['commission_type'] === 'fixed' ? ($data['currency'] ?? 'IDR') : null,
                        'is_active' => true,
                    ]
                );
            } else {
                // If commission_type is empty/null, delete existing config
                $unit->commissionConfig()?->delete();
            }

            // Touch the unit to update its updated_at timestamp
            $unit->touch();

            return $unit->fresh(['commissionConfig']);
        });
    }
}
