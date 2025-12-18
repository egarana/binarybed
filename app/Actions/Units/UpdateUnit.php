<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Support\Facades\DB;

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
            $shouldSyncFeatures = array_key_exists('features', $data) ||
                (isset($data['_features_cleared']) && $data['_features_cleared'] === '1');

            if ($shouldSyncFeatures) {
                $featuresData = (isset($data['_features_cleared']) && $data['_features_cleared'] === '1')
                    ? []
                    : ($data['features'] ?? []);

                // Filter out empty/null values
                $featuresData = array_values(array_filter($featuresData, fn($v) => $v !== '' && $v !== null));

                // OPTIMIZED: Batch fetch all features at once instead of N queries
                if (!empty($featuresData)) {
                    $centralFeatures = \App\Models\Feature::whereIn('id', $featuresData)->get()->keyBy('id');

                    // Batch upsert to tenant database
                    $upsertData = [];
                    foreach ($featuresData as $featureId) {
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
                }

                // Build sync data with order
                $features = collect($featuresData)->mapWithKeys(function ($featureId, $index) {
                    return [$featureId => [
                        'order' => $index,
                        'assigned_at' => now(),
                    ]];
                })->toArray();

                $updatedUnit->features()->sync($features);
                $updatedUnit->touch();
            }

            // Sync images (handle add, delete, and reorder)
            $shouldSyncImages = array_key_exists('existing_images', $data) ||
                array_key_exists('new_images', $data) ||
                array_key_exists('uploaded_media_ids', $data) ||
                (isset($data['_images_cleared']) && $data['_images_cleared'] === '1');

            if ($shouldSyncImages) {
                if (isset($data['_images_cleared']) && $data['_images_cleared'] === '1') {
                    $updatedUnit->clearMediaCollection('images');
                } else {
                    $existingImageIds = $data['existing_images'] ?? [];

                    // Get media to delete
                    $mediaToDelete = $updatedUnit->getMedia('images')
                        ->filter(fn($media) => !in_array($media->id, $existingImageIds));

                    if ($mediaToDelete->isNotEmpty()) {
                        // Collect file paths for async deletion
                        $filePaths = [];
                        foreach ($mediaToDelete as $media) {
                            // Get the file path(s) to delete
                            $filePaths[] = $media->getPath();
                            // Also get conversion paths if any
                            foreach ($media->generated_conversions ?? [] as $conversionName => $generated) {
                                if ($generated) {
                                    $filePaths[] = $media->getPath($conversionName);
                                }
                            }
                        }

                        // Delete database records immediately (fast)
                        \Spatie\MediaLibrary\MediaCollections\Models\Media::whereIn('id', $mediaToDelete->pluck('id'))
                            ->delete();

                        // Dispatch job to delete files from R2 in background (slow operation)
                        if (!empty($filePaths)) {
                            \App\Jobs\DeleteMediaFilesJob::dispatch($filePaths, config('media-library.disk_name', 'r2'));
                        }
                    }

                    // OPTIMIZED: Batch update order_column with a single query using CASE
                    if (!empty($existingImageIds)) {
                        $cases = [];
                        $ids = [];
                        foreach ($existingImageIds as $index => $mediaId) {
                            $cases[] = "WHEN id = {$mediaId} THEN " . ($index + 1);
                            $ids[] = $mediaId;
                        }

                        if (!empty($cases)) {
                            $caseStatement = implode(' ', $cases);
                            DB::statement("UPDATE media SET order_column = CASE {$caseStatement} END WHERE id IN (" . implode(',', $ids) . ")");
                        }
                    }
                }

                $maxOrder = count($data['existing_images'] ?? []);

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
                            $tenantMediaClass = config('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);

                            $newMedia = new $tenantMediaClass();
                            $newMedia->model_type = get_class($updatedUnit);
                            $newMedia->model_id = $updatedUnit->id;
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
                            $newMedia->order_column = $maxOrder + $index + 1;
                            $newMedia->save();

                            // Delete central media record (file stays on disk)
                            DB::connection('mysql')
                                ->table('media')
                                ->where('id', $centralMedia->id)
                                ->delete();

                            $tempUpload->delete();
                        }
                    }
                }

                // Handle legacy direct file upload if provided (fallback)
                if (isset($data['new_images']) && is_array($data['new_images'])) {
                    $newImageOffset = count($data['uploaded_media_ids'] ?? []);
                    foreach ($data['new_images'] as $index => $image) {
                        if ($image instanceof \Illuminate\Http\UploadedFile) {
                            $updatedUnit->addMedia($image)
                                ->withCustomProperties(['order' => $maxOrder + $newImageOffset + $index + 1])
                                ->toMediaCollection('images');
                        }
                    }
                }

                $updatedUnit->touch();
            }

            return $updatedUnit;
        });
    }
}
