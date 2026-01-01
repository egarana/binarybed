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

        // Get platform owner from central database BEFORE entering tenant context
        // Because roles table is in central database, not tenant database
        $platformOwner = \App\Models\User::role('super-admin')->first();

        return $this->executeInTenantContext($tenantId, function () use ($data, $platformOwner) {
            $activity = $this->activityRepository->create($data);

            // Attach features if provided
            if (isset($data['features']) && is_array($data['features'])) {
                // Filter out empty/null values
                $featureIds = array_values(array_filter($data['features'], fn($v) => $v !== '' && $v !== null));

                if (!empty($featureIds)) {
                    // OPTIMIZED: Batch fetch all features at once
                    $centralFeatures = \App\Models\Feature::whereIn('id', $featureIds)->get()->keyBy('id');

                    // Batch upsert to tenant database
                    $upsertData = [];
                    foreach ($featureIds as $featureId) {
                        $centralFeature = $centralFeatures->get($featureId);
                        if ($centralFeature) {
                            $upsertData[] = [
                                'feature_id' => $centralFeature->id,
                                'name' => $centralFeature->name,
                                'value' => $centralFeature->value,
                                'description' => $centralFeature->description,
                                'icon' => $centralFeature->icon,
                                'category' => $centralFeature->category,
                            ];
                        }
                    }

                    if (!empty($upsertData)) {
                        \App\Models\ResourceFeature::upsert(
                            $upsertData,
                            ['feature_id'],
                            ['name', 'value', 'description', 'icon', 'category']
                        );
                    }

                    // Attach with order
                    $features = collect($featureIds)->mapWithKeys(function ($featureId, $index) {
                        return [$featureId => [
                            'order' => $index,
                            'assigned_at' => now(),
                        ]];
                    })->toArray();

                    $activity->features()->sync($features);
                }
            }

            // Create default Standard Rate
            $activity->rates()->create([
                'name' => 'Standard Rate',
                'slug' => 'standard-rate',
                'price' => $data['standard_rate_price'] ?? 0,
                'currency' => $data['standard_rate_currency'] ?? 'IDR',
                'price_type' => $data['standard_rate_price_type'] ?? 'flat',
                'is_default' => true,
                'is_active' => true,
            ]);

            // Auto-assign platform owner (super-admin) with 100% commission split
            if ($platformOwner) {
                \App\Services\UserSyncService::attachActivityToUser(
                    centralUserId: $platformOwner->global_id,
                    activity: $activity,
                    pivotData: [
                        'role' => 'platform',
                        'commission_split' => 100.00,
                        'is_protected' => true,
                    ]
                );
            }

            // Handle pre-uploaded media (immediate upload feature)
            if (isset($data['uploaded_media_ids']) && is_array($data['uploaded_media_ids']) && !empty($data['uploaded_media_ids'])) {
                // OPTIMIZED: Batch fetch all temp uploads at once
                $tempUploads = \App\Models\TemporaryUpload::with('media')
                    ->whereIn('id', $data['uploaded_media_ids'])
                    ->get()
                    ->keyBy('id');

                foreach ($data['uploaded_media_ids'] as $index => $tempUploadId) {
                    $tempUpload = $tempUploads->get($tempUploadId);
                    if ($tempUpload && $tempUpload->media->isNotEmpty()) {
                        $centralMedia = $tempUpload->media->first();

                        // FAST: Instead of move() which copies files, we just copy the DB record
                        // Since we use RootBucketPathGenerator, files are stored with UUID names
                        // at the root bucket and don't need to be physically moved
                        $tenantMediaClass = config('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);

                        $newMedia = new $tenantMediaClass();
                        $newMedia->model_type = get_class($activity);
                        $newMedia->model_id = $activity->id;
                        $newMedia->uuid = $centralMedia->uuid;
                        $newMedia->collection_name = 'images';
                        $newMedia->name = $centralMedia->name;
                        $newMedia->file_name = $centralMedia->file_name;
                        $newMedia->mime_type = $centralMedia->mime_type;
                        $newMedia->disk = $centralMedia->disk;
                        $newMedia->conversions_disk = $centralMedia->conversions_disk;
                        $newMedia->size = $centralMedia->size;
                        $newMedia->manipulations = $centralMedia->manipulations ?? [];
                        $newMedia->custom_properties = $centralMedia->custom_properties ?? [];
                        $newMedia->generated_conversions = $centralMedia->generated_conversions ?? [];
                        $newMedia->responsive_images = $centralMedia->responsive_images ?? [];
                        $newMedia->order_column = $index + 1;
                        $newMedia->save();

                        // Delete central media record (file stays on disk)
                        \Illuminate\Support\Facades\DB::connection('mysql')
                            ->table('media')
                            ->where('id', $centralMedia->id)
                            ->delete();

                        // Delete the temporary upload record
                        $tempUpload->delete();
                    }
                }
            }

            // Handle legacy direct file upload if provided (fallback)
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
