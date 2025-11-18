<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $user = User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Ega Rana',
                'email' => 'bimansaegarana@gmail.com',
                'password' => Hash::make('Letdareca1#8'),
            ]);

        $user->assignRole('super-admin');
    }
}