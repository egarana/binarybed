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
            $activity = Activity::where('slug', $slug)->with('features')->firstOrFail();

            return [
                'id' => $activity->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $activity->name,
                'slug' => $activity->slug,
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
}
