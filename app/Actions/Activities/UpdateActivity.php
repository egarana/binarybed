<?php

namespace App\Actions\Activities;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Repositories\ActivityRepository;

class UpdateActivity
{
    use HandlesTenancy;

    public function __construct(
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Update a activity in the specified tenant's database.
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

            $updatedActivity = $this->activityRepository->update($activity, $data);

            // Sync features (handle attach, detach, and reorder)
            // Check if features array exists OR if _features_cleared flag is set
            $shouldSyncFeatures = array_key_exists('features', $data) ||
                (isset($data['_features_cleared']) && $data['_features_cleared'] === '1');

            if ($shouldSyncFeatures) {
                // If _features_cleared flag is set, use empty array
                $featuresData = (isset($data['_features_cleared']) && $data['_features_cleared'] === '1')
                    ? []
                    : ($data['features'] ?? []);

                // Sync features from central to tenant database first
                foreach ($featuresData as $featureId) {
                    $centralFeature = \App\Models\Feature::find($featureId);
                    if ($centralFeature) {
                        \App\Models\ResourceFeature::updateOrCreate(
                            ['feature_id' => $centralFeature->id],
                            [
                                'name' => $centralFeature->name,
                                'value' => $centralFeature->value,
                                'description' => $centralFeature->description,
                                'icon' => $centralFeature->icon,
                                'category' => $centralFeature->category,
                            ]
                        );
                    }
                }

                // Build sync data with order
                $features = collect($featuresData)->mapWithKeys(function ($featureId, $index) {
                    return [$featureId => [
                        'order' => $index,
                        'assigned_at' => now(),
                    ]];
                })->toArray();

                // Sync features (will attach new, detach removed, update existing)
                // Empty array will detach all features
                $updatedActivity->features()->sync($features);

                // Touch activity to update updated_at timestamp
                $updatedActivity->touch();
            }

            return $updatedActivity;
        });
    }
}
