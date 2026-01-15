<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Feature;
use App\Models\Activity;

class FindActivityByTenantAndSlug
{
    use HandlesTenancy;

    /**
     * Find a activity by tenant ID and slug.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function execute(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $activity = Activity::where('slug', $slug)->with(['features', 'commissionConfig'])->firstOrFail();

            return [
                'id' => $activity->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $activity->name,
                'slug' => $activity->slug,
                'subtitle' => $activity->subtitle,
                'description' => $activity->description,
                'highlights' => $activity->highlights,
                'selling_points' => $activity->selling_points,
                'book_direct_benefits' => $activity->book_direct_benefits,
                'location' => $activity->location,
                'rules' => $activity->rules,
                'host' => $activity->host,
                'commission_config' => $activity->commissionConfig ? [
                    'commission_type' => $activity->commissionConfig->commission_type,
                    'commission_percentage' => $activity->commissionConfig->commission_percentage,
                    'commission_fixed' => $activity->commissionConfig->commission_fixed,
                    'currency' => $activity->commissionConfig->currency ?? 'IDR',
                ] : null,
                'features' => $activity->features->map(function ($feature) {
                    return [
                        'value' => (string) $feature->feature_id, // Use feature_id untuk SearchableSelect
                        'label' => $feature->name,
                        'icon' => $feature->icon,
                    ];
                })->toArray(),
                'images' => $activity->getMedia('images')->map(function ($media) {
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
     * Find an activity with features grouped by category for Features management page.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function executeForFeatures(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $activity = Activity::where('slug', $slug)->with(['features'])->firstOrFail();

            // Group features by category
            $featuresByCategory = $activity->features->groupBy(function ($feature) {
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
                'id' => $activity->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $activity->name,
                'slug' => $activity->slug,
                'features' => $featuresByCategory,
            ];
        });
    }
}
