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
            if (array_key_exists('features', $data)) {
                $featuresData = $data['features'] ?? [];

                // Sync features from central to tenant database first (only if not empty)
                if (!empty($featuresData)) {
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
                } else {
                    // Empty array = detach all features
                    $features = [];
                }

                // Sync features (will attach new, detach removed, update existing)
                $updatedUnit->features()->sync($features);

                // Touch unit to update updated_at timestamp
                $updatedUnit->touch();
            }

            return $updatedUnit;
        });
    }
}
