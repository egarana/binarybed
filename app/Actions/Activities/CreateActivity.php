<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Repositories\ActivityRepository;
use App\HandlesTenancy;

class CreateActivity
{
    use HandlesTenancy;

    public function __construct(
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Create a new activity in the specified tenant's database.
     *
     * @param array $data
     * @return Activity
     */
    public function execute(array $data): Activity
    {
        $tenantId = $data['tenant_id'];

        return $this->executeInTenantContext($tenantId, function () use ($data) {
            $activity = $this->activityRepository->create($data);

            // Attach features if provided
            if (isset($data['features']) && is_array($data['features'])) {
                // Sync features from central to tenant database first
                foreach ($data['features'] as $featureId) {
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

                // Now attach with order
                $features = collect($data['features'])->mapWithKeys(function ($featureId, $index) {
                    return [$featureId => [
                        'order' => $index,
                        'assigned_at' => now(),
                    ]];
                })->toArray();

                $activity->features()->sync($features);
            }

            // Handle multiple images upload if provided
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $activity->addMedia($image)
                            ->toMediaCollection('images');
                    }
                }
            }

            return $activity;
        });
    }
}
