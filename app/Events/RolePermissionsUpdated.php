<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use App\Models\Role;
use App\Models\User;

class RolePermissionsUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Role $role,
        public array $addedPermissions,
        public array $removedPermissions,
        public User $causer
    ) {}
}
