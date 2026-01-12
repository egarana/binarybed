<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            FeatureSeeder::class,
        ]);

        $user = User::firstOrCreate([
            'email' => 'bimansaegarana@gmail.com',
        ], [
            'global_id' => Str::uuid(),
            'name' => 'Bali Simfoni Eksplorasi',
            'password' => Hash::make('Letdareca1#8'),
        ]);

        $user->assignRole('super-admin');

        $this->call([
            DummyDataSeeder::class,
        ]);
    }
}
