<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\Jobs\DeleteMediaFilesJob;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository
    ) {}

    /**
     * Delete a unit in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $slug
     * @return void
     */
    public function execute(string $tenantId, string $slug): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            // Get all media for this unit BEFORE deleting
            $allMedia = $unit->getMedia('images');

            if ($allMedia->isNotEmpty()) {
                // Collect file paths for async deletion
                $filePaths = [];
                foreach ($allMedia as $media) {
                    // Get the main file path
                    $filePaths[] = $media->getPath();
                    // Also get conversion paths if any
                    foreach ($media->generated_conversions ?? [] as $conversionName => $generated) {
                        if ($generated) {
                            $filePaths[] = $media->getPath($conversionName);
                        }
                    }
                }

                // Delete database records immediately (fast) - bypass Spatie auto-delete
                Media::whereIn('id', $allMedia->pluck('id'))->delete();

                // Dispatch job to delete files from R2 in background (slow operation)
                if (!empty($filePaths)) {
                    DeleteMediaFilesJob::dispatch($filePaths, config('media-library.disk_name', 'r2'));
                }
            }

            // Now delete the model (media already cleaned up, so Spatie won't try to delete files)
            $this->unitRepository->delete($unit);
        });
    }
}
