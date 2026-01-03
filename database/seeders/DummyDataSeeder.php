<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserTenant;
use App\Models\Rate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder creates exact data matching the current database state.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting DummyDataSeeder...');

        // Step 1: Ensure the main user exists with super-admin role
        $user = $this->createMainUser();

        // Step 2: Create the tenant with domain
        $tenant = $this->createTenant();

        // Step 3: Link user to tenant
        $this->linkUserToTenant($user, $tenant);

        // Step 4: Seed the tenant database
        $this->seedTenantDatabase($tenant, $user);

        $this->command->info('✅ DummyDataSeeder completed successfully!');
    }

    /**
     * Create the main user in central database
     */
    private function createMainUser(): User
    {
        $this->command->info('👤 Creating main user...');

        $user = User::firstOrCreate(
            ['email' => 'bimansaegarana@gmail.com'],
            [
                'global_id' => '255108f1-dc4d-4217-a611-018f8fa0bf0a',
                'name' => 'Bali Simfoni Eksplorasi',
                'password' => Hash::make('Letdareca1#8'),
                'email_verified_at' => now(),
            ]
        );

        // Assign super-admin role if not already assigned
        if (!$user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        $this->command->info("   User created: {$user->email}");
        return $user;
    }

    /**
     * Create the tenant with domain
     */
    private function createTenant(): Tenant
    {
        $this->command->info('🏢 Creating tenant...');

        // Check if tenant already exists
        $tenant = Tenant::find('lakebaturcabin');
        if ($tenant) {
            $this->command->warn('   Tenant lakebaturcabin already exists, skipping...');
            return $tenant;
        }

        $tenant = Tenant::create([
            'id' => 'lakebaturcabin',
            'name' => 'Lake Batur Cabin',
            'data' => [
                'resource_routes' => [
                    'cabins' => 'units',
                    'experiences' => 'activities',
                ],
            ],
        ]);

        $tenant->domains()->create([
            'domain' => 'lakebaturcabin.test',
        ]);

        $this->command->info("   Created tenant: Lake Batur Cabin");
        return $tenant;
    }

    /**
     * Link user to tenant
     */
    private function linkUserToTenant(User $user, Tenant $tenant): void
    {
        $this->command->info('🔗 Linking user to tenant...');

        DB::table('tenant_users')->insertOrIgnore([
            'tenant_id' => $tenant->id,
            'global_user_id' => $user->global_id,
        ]);
    }

    /**
     * Seed data for the tenant database
     */
    private function seedTenantDatabase(Tenant $tenant, User $user): void
    {
        $this->command->info("📦 Seeding tenant: {$tenant->name}...");

        tenancy()->initialize($tenant);

        try {
            // 1. Create tenant user
            $this->createTenantUser($user);

            // 2. Create units
            $this->createUnits($user);

            // 3. Create activities
            $this->createActivities($user);

            $this->command->info('   Tenant seeding completed');
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Create user in tenant database
     */
    private function createTenantUser(User $centralUser): void
    {
        UserTenant::updateOrCreate(
            ['global_id' => $centralUser->global_id],
            [
                'name' => $centralUser->name,
                'email' => $centralUser->email,
            ]
        );

        $this->command->info('   Created tenant user');
    }

    /**
     * Create units with rates, resource_users, and commission configs
     */
    private function createUnits(User $user): void
    {
        $unitsData = [
            [
                'name' => 'Rahajeng Cabin',
                'slug' => 'rahajeng-cabin',
                'rate_price' => 450000,
                'commission_type' => 'percentage',
                'commission_percentage' => 5.00,
                'commission_fixed' => null,
            ],
            [
                'name' => 'Rahayu Cabin',
                'slug' => 'rahayu-cabin',
                'rate_price' => 700000,
                'commission_type' => 'percentage',
                'commission_percentage' => 5.00,
                'commission_fixed' => null,
            ],
        ];

        foreach ($unitsData as $unitData) {
            // Check if unit already exists
            $existingUnit = Unit::where('slug', $unitData['slug'])->first();
            if ($existingUnit) {
                $this->command->warn("   Unit {$unitData['name']} already exists, skipping...");
                continue;
            }

            $unit = Unit::create([
                'name' => $unitData['name'],
                'slug' => $unitData['slug'],
            ]);

            // Create rate
            Rate::create([
                'rateable_type' => Unit::class,
                'rateable_id' => $unit->id,
                'name' => 'Standard Rate',
                'slug' => 'standard-rate',
                'description' => null,
                'price' => $unitData['rate_price'],
                'currency' => 'IDR',
                'price_type' => 'night',
                'is_active' => true,
                'is_default' => true,
            ]);

            // Create resource_user (platform role)
            DB::table('resource_users')->insert([
                'global_id' => $user->global_id,
                'resourceable_type' => Unit::class,
                'resourceable_id' => $unit->id,
                'role' => 'platform',
                'commission_split' => 100.00,
                'is_protected' => true,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create resource_commission_config
            DB::table('resource_commission_configs')->insert([
                'resourceable_type' => Unit::class,
                'resourceable_id' => $unit->id,
                'commission_type' => $unitData['commission_type'],
                'commission_percentage' => $unitData['commission_percentage'],
                'commission_fixed' => $unitData['commission_fixed'],
                'currency' => 'IDR',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('   Created ' . count($unitsData) . ' units with rates, resource_users, and commission configs');
    }

    /**
     * Create activities with rates, resource_users, and commission configs
     */
    private function createActivities(User $user): void
    {
        $activitiesData = [
            [
                'name' => 'Boat Cruising in Batur Lake',
                'slug' => 'boat-cruising-in-batur-lake',
                'rate_price' => 250000,
                'price_type' => 'boat',
                'commission_type' => 'fixed',
                'commission_percentage' => null,
                'commission_fixed' => 50000,
            ],
        ];

        foreach ($activitiesData as $activityData) {
            // Check if activity already exists
            $existingActivity = Activity::where('slug', $activityData['slug'])->first();
            if ($existingActivity) {
                $this->command->warn("   Activity {$activityData['name']} already exists, skipping...");
                continue;
            }

            $activity = Activity::create([
                'name' => $activityData['name'],
                'slug' => $activityData['slug'],
            ]);

            // Create rate
            Rate::create([
                'rateable_type' => Activity::class,
                'rateable_id' => $activity->id,
                'name' => 'Standard Rate',
                'slug' => 'standard-rate',
                'description' => null,
                'price' => $activityData['rate_price'],
                'currency' => 'IDR',
                'price_type' => $activityData['price_type'],
                'is_active' => true,
                'is_default' => true,
            ]);

            // Create resource_user (platform role)
            DB::table('resource_users')->insert([
                'global_id' => $user->global_id,
                'resourceable_type' => Activity::class,
                'resourceable_id' => $activity->id,
                'role' => 'platform',
                'commission_split' => 100.00,
                'is_protected' => true,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create resource_commission_config
            DB::table('resource_commission_configs')->insert([
                'resourceable_type' => Activity::class,
                'resourceable_id' => $activity->id,
                'commission_type' => $activityData['commission_type'],
                'commission_percentage' => $activityData['commission_percentage'],
                'commission_fixed' => $activityData['commission_fixed'],
                'currency' => 'IDR',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('   Created ' . count($activitiesData) . ' activities with rates, resource_users, and commission configs');
    }
}
