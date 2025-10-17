<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\CreateTenantAction;

class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create {id} {name} {domain}';
    protected $description = 'Create a new tenant via CLI';

    public function handle(CreateTenantAction $action): int
    {
        $id = $this->argument('id');
        $name = $this->argument('name');
        $domain = $this->argument('domain');

        $tenant = $action->execute($id, $name, $domain);

        $this->info("✅ Tenant [{$tenant->name}] created successfully!");
        $this->info("🌐 Domain: {$tenant->domains->first()->domain}");
        $this->info("🆔 ID: {$tenant->id}");
        return self::SUCCESS;
    }
}
