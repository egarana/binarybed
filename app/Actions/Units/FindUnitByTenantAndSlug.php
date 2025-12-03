<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Feature;
use App\Models\Unit;

class FindUnitByTenantAndSlug
{
    use HandlesTenancy;

    /**
     * Find a unit by tenant ID and slug.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function execute(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $unit = Unit::where('slug', $slug)->with('features')->firstOrFail();

            return [
                'id' => $unit->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $unit->name,
                'slug' => $unit->slug,
                'features' => $unit->features->map(function ($feature) {
                    return [
                        'value' => (string) $feature->feature_id, // Use feature_id untuk SearchableSelect
                        'label' => $feature->name,
                        'icon' => $feature->icon,
                    ];
                })->toArray(),
            ];
        });
    }
}
