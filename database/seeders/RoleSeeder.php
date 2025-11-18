<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super-admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'partner',
                'guard_name' => 'web',
            ],
            [
                'name' => 'referrer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'business-owner',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                $role
            );
        }
    }
}