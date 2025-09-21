<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            FeatureSeeder::class,
            CountrySeeder::class,
        ]);
        
        // 1. Ensure the "super-admin" role exists
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
        ]);

        sleep(1); // optional delay

        // 2. Create the Super Admin user
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'bimansaegarana@gmail.com',
            'password' => Hash::make('Letdareca1#8'),
        ]);

        sleep(1); // optional delay

        // 3. Assign the role
        $user->assignRole($superAdminRole);

        // 4. Log activity manually
        activity()
            ->causedBy($user) // or you can replace with a system user if needed
            ->performedOn($user)
            ->withProperties([
                'roles' => [$superAdminRole->name],
                'user_name' => $user->name,
                'user_email' => $user->email,
            ])
            ->log('Assigned role(s): <span class="font-semibold">' . $superAdminRole->name . '</span> to user <span class="font-semibold">' . $user->name . '</span> by <span class="font-semibold">System (system@local)</span>.');
    }
}
