<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Repositories\UnitRepository;

class UpdateUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository
    ) {}

    /**
     * Update a unit in the specified tenant's database.
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

            $updatedUnit = $this->unitRepository->update($unit, $data);

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
                $updatedUnit->features()->sync($features);

                // Touch unit to update updated_at timestamp
                $updatedUnit->touch();
            }

            // Sync images (handle add, delete, and reorder)
            $shouldSyncImages = array_key_exists('existing_images', $data) ||
                array_key_exists('new_images', $data) ||
                (isset($data['_images_cleared']) && $data['_images_cleared'] === '1');

            if ($shouldSyncImages) {
                // Clear all images if flag is set
                if (isset($data['_images_cleared']) && $data['_images_cleared'] === '1') {
                    $updatedUnit->clearMediaCollection('images');
                } else {
                    // Get IDs of images to keep
                    $existingImageIds = $data['existing_images'] ?? [];

                    // Delete images not in the existing list
                    $updatedUnit->getMedia('images')
                        ->filter(fn($media) => !in_array($media->id, $existingImageIds))
                        ->each(fn($media) => $media->delete());
                }

                // Add new images
                if (isset($data['new_images']) && is_array($data['new_images'])) {
                    foreach ($data['new_images'] as $image) {
                        if ($image instanceof \Illuminate\Http\UploadedFile) {
                            $updatedUnit->addMedia($image)
                                ->toMediaCollection('images');
                        }
                    }
                }

                // Touch unit to update updated_at timestamp
                $updatedUnit->touch();
            }

            return $updatedUnit;
        });
    }
}
