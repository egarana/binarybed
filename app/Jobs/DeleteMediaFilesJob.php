<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Job to delete media files from storage asynchronously.
 * This avoids blocking the HTTP request when deleting files from slow
 * cloud storage like R2/S3.
 */
class DeleteMediaFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param array $filePaths Array of file paths to delete
     * @param string $disk The storage disk name
     */
    public function __construct(
        public array $filePaths,
        public string $disk = 'r2'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $storage = Storage::disk($this->disk);
        $deleted = 0;
        $errors = 0;

        foreach ($this->filePaths as $path) {
            try {
                if ($storage->exists($path)) {
                    $storage->delete($path);
                    $deleted++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to delete media file: {$path}", [
                    'disk' => $this->disk,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        Log::info("DeleteMediaFilesJob completed", [
            'total' => count($this->filePaths),
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("DeleteMediaFilesJob failed", [
            'files' => $this->filePaths,
            'disk' => $this->disk,
            'error' => $exception->getMessage(),
        ]);
    }
}
