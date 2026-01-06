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
            $unit = Unit::where('slug', $slug)->with(['features', 'commissionConfig'])->firstOrFail();

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
                'features' => $unit->features->map(function ($feature) {
                    return [
                        'value' => (string) $feature->feature_id, // Use feature_id untuk SearchableSelect
                        'label' => $feature->name,
                        'icon' => $feature->icon,
                    ];
                })->toArray(),
                'images' => $unit->getMedia('images')->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'name' => $media->file_name,
                        'size' => $media->size,
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Find a unit with features grouped by category for Features management page.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function executeForFeatures(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $unit = Unit::where('slug', $slug)->with(['features'])->firstOrFail();

            // Group features by category
            $featuresByCategory = $unit->features->groupBy(function ($feature) {
                return $feature->pivot->category ?? 'amenity';
            })->map(function ($features) {
                return $features->map(function ($feature) {
                    return [
                        'value' => (string) $feature->feature_id,
                        'label' => $feature->name,
                        'icon' => $feature->icon,
                    ];
                })->values()->toArray();
            })->toArray();

            return [
                'id' => $unit->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $unit->name,
                'slug' => $unit->slug,
                'features' => $featuresByCategory,
            ];
        });
    }
}
