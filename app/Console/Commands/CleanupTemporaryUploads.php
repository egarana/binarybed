<?php

namespace App\Console\Commands;

use App\Models\TemporaryUpload;
use Illuminate\Console\Command;

class CleanupTemporaryUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup 
                            {--hours=24 : Delete uploads older than this many hours}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned temporary uploads that were never attached to a model';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        $query = TemporaryUpload::olderThan($hours);
        $count = $query->count();

        if ($count === 0) {
            $this->info('No temporary uploads to clean up.');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Would delete {$count} temporary upload(s) older than {$hours} hours.");
            return Command::SUCCESS;
        }

        $this->info("Cleaning up {$count} temporary upload(s) older than {$hours} hours...");

        $deleted = 0;
        $errors = 0;

        // Process in chunks to avoid memory issues
        $query->chunk(100, function ($uploads) use (&$deleted, &$errors) {
            foreach ($uploads as $upload) {
                try {
                    // Delete associated media files
                    $upload->clearMediaCollection('default');
                    $upload->delete();
                    $deleted++;
                } catch (\Exception $e) {
                    $this->error("Failed to delete upload #{$upload->id}: {$e->getMessage()}");
                    $errors++;
                }
            }
        });

        $this->info("Cleanup complete. Deleted: {$deleted}, Errors: {$errors}");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
