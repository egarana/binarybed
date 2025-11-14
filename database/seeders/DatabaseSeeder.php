<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Ega Rana',
                'email' => 'bimansaegarana@gmail.com',
                'password' => Hash::make('Letdareca1#8'),
            ]);

        $user->assignRole($role);
    }
}
