<?php

namespace App\Actions\Units;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\HandlesTenancy;

class CreateUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository
    ) {}

    /**
     * Create a new unit in the specified tenant's database.
     *
     * @param array $data
     * @return Unit
     */
    public function execute(array $data): Unit
    {
        $tenantId = $data['tenant_id'];

        return $this->executeInTenantContext($tenantId, function () use ($data) {
            $unit = $this->unitRepository->create($data);

            // Attach features if provided
            if (isset($data['features']) && is_array($data['features']) && !empty($data['features'])) {
                // OPTIMIZED: Batch fetch all features at once
                $centralFeatures = \App\Models\Feature::whereIn('id', $data['features'])->get()->keyBy('id');

                // Batch upsert to tenant database
                $upsertData = [];
                foreach ($data['features'] as $featureId) {
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
                $features = collect($data['features'])->mapWithKeys(function ($featureId, $index) {
                    return [$featureId => [
                        'order' => $index,
                        'assigned_at' => now(),
                    ]];
                })->toArray();

                $unit->features()->sync($features);
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
                        $newMedia->model_type = get_class($unit);
                        $newMedia->model_id = $unit->id;
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

                        // Delete central media record (file stays on disk since we don't use delete())
                        // Use forceDelete to skip file deletion, or just delete the record directly
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
                        $unit->addMedia($image)
                            ->toMediaCollection('images');
                    }
                }
            }

            return $unit;
        });
    }
}
