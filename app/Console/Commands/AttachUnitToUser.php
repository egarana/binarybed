<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Console\Command;

class AttachUnitToUser extends Command
{
    protected $signature = 'user:attach-unit {email} {tenant} {unit-slug}';
    
    public function handle()
    {
        $centralUser = User::where('email', $this->argument('email'))->first();
        $tenant = Tenant::find($this->argument('tenant'));
        
        $tenant->run(function () use ($centralUser) {
            $unit = Unit::where('slug', $this->argument('unit-slug'))->first();
            UserSyncService::attachUnitToUser($centralUser->id, $unit);
            $this->info("✅ Unit '{$unit->name}' attached to {$centralUser->name}");
        });
    }
}
