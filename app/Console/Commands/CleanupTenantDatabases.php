<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupTenantDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:cleanup-databases
                            {--force : Force deletion without confirmation}
                            {--all : Delete all tenant databases including active ones}
                            {--database=* : Specific database name(s) to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up tenant databases that start with binarybed_';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Scanning for tenant databases...');

        // Get all databases starting with binarybed_
        $prefix = config('tenancy.database.prefix', 'binarybed_');
        $databases = $this->getTenantDatabases($prefix);

        if ($databases->isEmpty()) {
            $this->info('✅ No tenant databases found.');
            return self::SUCCESS;
        }

        $this->info("📋 Found {$databases->count()} database(s) with prefix '{$prefix}'");
        $this->newLine();

        // Get active tenant database names
        $activeTenants = Tenant::all()->pluck('tenancy_db_name')->filter();

        // Separate orphaned and active databases
        $orphanedDatabases = $databases->reject(fn($db) => $activeTenants->contains($db));
        $activeDatabases = $databases->intersect($activeTenants);

        // Display status
        $this->displayDatabaseStatus($orphanedDatabases, $activeDatabases);

        // Determine which databases to delete
        $databasesToDelete = $this->getDatabasesForDeletion(
            $databases,
            $orphanedDatabases,
            $activeDatabases
        );

        if ($databasesToDelete->isEmpty()) {
            $this->info('ℹ️  No databases selected for deletion.');
            return self::SUCCESS;
        }

        // Confirm deletion
        if (!$this->option('force')) {
            $this->warn('⚠️  This action will permanently delete the following databases:');
            foreach ($databasesToDelete as $database) {
                $this->line("   • {$database}");
            }
            $this->newLine();
            // Auto-proceed as per user request (removed confirmation)
        }

        // Delete databases
        $this->deleteDatabases($databasesToDelete);

        return self::SUCCESS;
    }

    /**
     * Get all tenant databases with the specified prefix.
     */
    protected function getTenantDatabases(string $prefix): \Illuminate\Support\Collection
    {
        try {
            $databases = DB::select('SHOW DATABASES');

            return collect($databases)
                ->pluck('Database')
                ->filter(fn($db) => str_starts_with($db, $prefix));
        } catch (\Exception $e) {
            $this->error("❌ Failed to fetch databases: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Display the status of databases.
     */
    protected function displayDatabaseStatus($orphanedDatabases, $activeDatabases): void
    {
        if ($activeDatabases->isNotEmpty()) {
            $this->info("🟢 Active tenant databases ({$activeDatabases->count()}):");
            foreach ($activeDatabases as $database) {
                $this->line("   ✓ {$database}");
            }
            $this->newLine();
        }

        if ($orphanedDatabases->isNotEmpty()) {
            $this->warn("🟡 Orphaned databases ({$orphanedDatabases->count()}):");
            foreach ($orphanedDatabases as $database) {
                $this->line("   ! {$database}");
            }
            $this->newLine();
        }
    }

    /**
     * Determine which databases should be deleted based on options.
     */
    protected function getDatabasesForDeletion($allDatabases, $orphanedDatabases, $activeDatabases): \Illuminate\Support\Collection
    {
        // If specific databases are provided
        if ($specificDatabases = $this->option('database')) {
            return collect($specificDatabases)->intersect($allDatabases);
        }

        // If --all flag is set, delete all databases
        if ($this->option('all')) {
            $this->warn('⚠️  --all flag is set. This will delete ALL tenant databases including active ones!');
            return $allDatabases;
        }

        // Default: only delete orphaned databases
        if ($orphanedDatabases->isEmpty()) {
            $this->info('✅ No orphaned databases found.');
            return collect();
        }

        return $orphanedDatabases;
    }

    /**
     * Delete the specified databases.
     */
    protected function deleteDatabases(\Illuminate\Support\Collection $databases): void
    {
        $this->info('🗑️  Deleting databases...');
        $bar = $this->output->createProgressBar($databases->count());
        $bar->start();

        $successCount = 0;
        $failedCount = 0;

        foreach ($databases as $database) {
            try {
                DB::statement("DROP DATABASE IF EXISTS `{$database}`");
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $this->newLine();
                $this->error("❌ Failed to delete '{$database}': {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        if ($successCount > 0) {
            $this->info("✅ Successfully deleted {$successCount} database(s)");
        }

        if ($failedCount > 0) {
            $this->error("❌ Failed to delete {$failedCount} database(s)");
        }
    }
}
