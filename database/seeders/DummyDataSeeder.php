<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Feature;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\ResourceFeature;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserTenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting DummyDataSeeder...');

        // Step 1: Create additional central users
        $users = $this->createCentralUsers();

        // Step 2: Create tenants with domains
        $tenants = $this->createTenants();

        // Step 3: Attach users to tenants
        $this->attachUsersToTenants($users, $tenants);

        // Step 4: Seed each tenant's database
        foreach ($tenants as $tenant) {
            $this->seedTenantDatabase($tenant);
        }

        $this->command->info('✅ DummyDataSeeder completed successfully!');
    }

    /**
     * Create additional users in central database
     */
    private function createCentralUsers(): array
    {
        $this->command->info('👤 Creating central users...');

        $faker = fake('id_ID');
        $users = [];

        // Create 6 users with different roles
        $userProfiles = [
            ['role' => 'business-owner', 'name' => $faker->name(), 'email' => 'owner1@demo.test'],
            ['role' => 'business-owner', 'name' => $faker->name(), 'email' => 'owner2@demo.test'],
            ['role' => 'partner', 'name' => $faker->name(), 'email' => 'partner1@demo.test'],
            ['role' => 'partner', 'name' => $faker->name(), 'email' => 'partner2@demo.test'],
            ['role' => 'referrer', 'name' => $faker->name(), 'email' => 'referrer1@demo.test'],
            ['role' => 'referrer', 'name' => $faker->name(), 'email' => 'referrer2@demo.test'],
        ];

        foreach ($userProfiles as $profile) {
            $user = User::factory()->withoutTwoFactor()->create([
                'global_id' => (string) Str::uuid(),
                'name' => $profile['name'],
                'email' => $profile['email'],
                'password' => Hash::make('password'),
            ]);
            $user->assignRole($profile['role']);
            $users[] = $user;
        }

        $this->command->info("   Created " . count($users) . " users");
        return $users;
    }

    /**
     * Create tenants with domains
     */
    private function createTenants(): array
    {
        $this->command->info('🏢 Creating tenants...');

        $tenantsData = [
            [
                'id' => 'villasunrise',
                'name' => 'Villa Sunrise Bali',
                'domain' => 'villasunrise.test',
                'data' => ['resource_routes' => ['units']],
            ],
            [
                'id' => 'divingadventure',
                'name' => 'Diving Adventure Indonesia',
                'domain' => 'divingadventure.test',
                'data' => ['resource_routes' => ['activities']],
            ],
            [
                'id' => 'baliparadiseresort',
                'name' => 'Bali Paradise Resort',
                'domain' => 'baliparadiseresort.test',
                'data' => ['resource_routes' => ['units', 'activities']],
            ],
        ];

        $tenants = [];

        foreach ($tenantsData as $data) {
            // Check if tenant already exists
            if (Tenant::find($data['id'])) {
                $this->command->warn("   Tenant {$data['id']} already exists, skipping...");
                continue;
            }

            $tenant = Tenant::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'data' => $data['data'],
            ]);

            $tenant->domains()->create([
                'domain' => $data['domain'],
            ]);

            $tenants[] = $tenant;
            $this->command->info("   Created tenant: {$data['name']}");
        }

        return $tenants;
    }

    /**
     * Attach users to tenants
     */
    private function attachUsersToTenants(array $users, array $tenants): void
    {
        $this->command->info('🔗 Attaching users to tenants...');

        foreach ($tenants as $tenant) {
            // Get super admin
            $superAdmin = User::where('email', 'bimansaegarana@gmail.com')->first();
            if ($superAdmin) {
                DB::table('tenant_users')->insertOrIgnore([
                    'tenant_id' => $tenant->id,
                    'global_user_id' => $superAdmin->global_id,
                ]);
            }

            // Attach random 3-4 users to each tenant
            $randomUsers = collect($users)->shuffle()->take(rand(3, 4));
            foreach ($randomUsers as $user) {
                DB::table('tenant_users')->insertOrIgnore([
                    'tenant_id' => $tenant->id,
                    'global_user_id' => $user->global_id,
                ]);
            }
        }
    }

    /**
     * Seed data for a specific tenant
     */
    private function seedTenantDatabase(Tenant $tenant): void
    {
        $this->command->info("📦 Seeding tenant: {$tenant->name}...");

        tenancy()->initialize($tenant);

        try {
            // 1. Sync central users to tenant database
            $this->syncUsersToTenant($tenant);

            // 2. Sync features from central to tenant
            $this->syncFeaturesToTenant();

            // 3. Create Units - always for demo purposes
            $units = $this->createUnits();

            // 4. Create Activities - always for demo purposes
            $activities = $this->createActivities();

            // 5. Create Rates for Units and Activities
            $this->createRates($units, $activities);

            // 6. Assign features to resources
            $this->assignFeatures($units, $activities);

            // 7. Assign users to resources
            $this->assignUsers($units, $activities);

            // 8. Create Reservations
            $this->createReservations($units, $activities);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Sync users from central to tenant database
     */
    private function syncUsersToTenant(Tenant $tenant): void
    {
        $centralConnection = config('tenancy.database.central_connection');

        $tenantUserIds = DB::connection($centralConnection)
            ->table('tenant_users')
            ->where('tenant_id', $tenant->id)
            ->pluck('global_user_id');

        $centralUsers = User::whereIn('global_id', $tenantUserIds)->get();

        foreach ($centralUsers as $centralUser) {
            UserTenant::updateOrCreate(
                ['global_id' => $centralUser->global_id],
                [
                    'name' => $centralUser->name,
                    'email' => $centralUser->email,
                ]
            );
        }

        $this->command->info("   Synced " . $centralUsers->count() . " users to tenant");
    }

    /**
     * Sync features from central database
     */
    private function syncFeaturesToTenant(): void
    {
        $centralFeatures = Feature::all();

        foreach ($centralFeatures as $feature) {
            ResourceFeature::updateOrCreate(
                ['feature_id' => $feature->id],
                [
                    'name' => $feature->name,
                    'value' => $feature->value,
                    'description' => $feature->description,
                    'icon' => $feature->icon,
                    'category' => $feature->category,
                ]
            );
        }

        $this->command->info("   Synced " . $centralFeatures->count() . " features");
    }

    /**
     * Create Units for tenant
     */
    private function createUnits(): array
    {
        $faker = fake('id_ID');
        $units = [];

        $unitTemplates = [
            ['name' => 'Deluxe Ocean View Suite', 'type' => 'suite'],
            ['name' => 'Garden Bungalow', 'type' => 'bungalow'],
            ['name' => 'Premium Pool Villa', 'type' => 'villa'],
            ['name' => 'Standard Room', 'type' => 'room'],
            ['name' => 'Family Suite', 'type' => 'suite'],
            ['name' => 'Honeymoon Villa', 'type' => 'villa'],
        ];

        // Create 4-6 random units
        $selectedTemplates = collect($unitTemplates)->shuffle()->take(rand(4, 6));

        foreach ($selectedTemplates as $template) {
            $unit = Unit::create([
                'name' => $template['name'],
                'slug' => Str::slug($template['name']) . '-' . Str::random(4),
            ]);
            $units[] = $unit;
        }

        $this->command->info("   Created " . count($units) . " units");
        return $units;
    }

    /**
     * Create Activities for tenant
     */
    private function createActivities(): array
    {
        $faker = fake('id_ID');
        $activities = [];

        $activityTemplates = [
            ['name' => 'Sunrise Scuba Diving Tour', 'type' => 'diving'],
            ['name' => 'Rice Terrace Cycling Adventure', 'type' => 'cycling'],
            ['name' => 'Temple Cultural Experience', 'type' => 'cultural'],
            ['name' => 'Snorkeling Paradise Trip', 'type' => 'snorkeling'],
            ['name' => 'Sunset Dolphin Watching', 'type' => 'marine'],
            ['name' => 'Mount Batur Sunrise Trek', 'type' => 'hiking'],
            ['name' => 'Traditional Cooking Class', 'type' => 'culinary'],
            ['name' => 'White Water Rafting', 'type' => 'adventure'],
        ];

        // Create 4-6 random activities
        $selectedTemplates = collect($activityTemplates)->shuffle()->take(rand(4, 6));

        foreach ($selectedTemplates as $template) {
            $activity = Activity::create([
                'name' => $template['name'],
                'slug' => Str::slug($template['name']) . '-' . Str::random(4),
            ]);
            $activities[] = $activity;
        }

        $this->command->info("   Created " . count($activities) . " activities");
        return $activities;
    }

    /**
     * Create Rates for Units and Activities
     */
    private function createRates(array $units, array $activities): void
    {
        $faker = fake('id_ID');
        $rateCount = 0;

        // Create rates for units
        foreach ($units as $unit) {
            // Standard nightly rate
            Rate::create([
                'rateable_type' => Unit::class,
                'rateable_id' => $unit->id,
                'name' => 'Standard Rate',
                'slug' => 'standard-rate',
                'description' => 'Regular nightly rate with breakfast included',
                'price' => $faker->numberBetween(500000, 2000000),
                'currency' => 'IDR',
                'price_type' => 'night',
                'is_active' => true,
                'is_default' => true,
            ]);
            $rateCount++;

            // Weekend rate (50% chance)
            if ($faker->boolean(50)) {
                Rate::create([
                    'rateable_type' => Unit::class,
                    'rateable_id' => $unit->id,
                    'name' => 'Weekend Special',
                    'slug' => 'weekend-special',
                    'description' => 'Special weekend rate with extra amenities',
                    'price' => $faker->numberBetween(700000, 2500000),
                    'currency' => 'IDR',
                    'price_type' => 'night',
                    'is_active' => true,
                    'is_default' => false,
                ]);
                $rateCount++;
            }
        }

        // Create rates for activities
        foreach ($activities as $activity) {
            // Per person rate
            Rate::create([
                'rateable_type' => Activity::class,
                'rateable_id' => $activity->id,
                'name' => 'Adult Rate',
                'slug' => 'adult-rate',
                'description' => 'Standard rate per adult participant',
                'price' => $faker->numberBetween(250000, 1500000),
                'currency' => 'IDR',
                'price_type' => 'person',
                'is_active' => true,
                'is_default' => true,
            ]);
            $rateCount++;

            // Group package (40% chance)
            if ($faker->boolean(40)) {
                Rate::create([
                    'rateable_type' => Activity::class,
                    'rateable_id' => $activity->id,
                    'name' => 'Group Package',
                    'slug' => 'group-package',
                    'description' => 'Special rate for groups of 4 or more',
                    'price' => $faker->numberBetween(800000, 4000000),
                    'currency' => 'IDR',
                    'price_type' => 'group',
                    'is_active' => true,
                    'is_default' => false,
                ]);
                $rateCount++;
            }
        }

        $this->command->info("   Created {$rateCount} rates");
    }

    /**
     * Assign features to Units and Activities
     */
    private function assignFeatures(array $units, array $activities): void
    {
        $amenityFeatures = ResourceFeature::where('category', 'amenity')->pluck('feature_id')->toArray();
        $facilityFeatures = ResourceFeature::where('category', 'facility')->pluck('feature_id')->toArray();
        $inclusionFeatures = ResourceFeature::where('category', 'inclusion')->pluck('feature_id')->toArray();
        $equipmentFeatures = ResourceFeature::where('category', 'equipment')->pluck('feature_id')->toArray();

        $assignmentCount = 0;

        // Assign amenity & facility features to units
        foreach ($units as $unit) {
            $selectedAmenities = collect($amenityFeatures)->shuffle()->take(rand(3, 6));
            $selectedFacilities = collect($facilityFeatures)->shuffle()->take(rand(2, 4));

            $order = 0;
            foreach ($selectedAmenities->merge($selectedFacilities) as $featureId) {
                DB::table('resource_features')->insertOrIgnore([
                    'feature_id' => $featureId,
                    'featureable_type' => Unit::class,
                    'featureable_id' => $unit->id,
                    'order' => $order++,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignmentCount++;
            }
        }

        // Assign inclusion & equipment features to activities
        foreach ($activities as $activity) {
            $selectedInclusions = collect($inclusionFeatures)->shuffle()->take(rand(2, 4));
            $selectedEquipment = collect($equipmentFeatures)->shuffle()->take(rand(1, 2));

            $order = 0;
            foreach ($selectedInclusions->merge($selectedEquipment) as $featureId) {
                DB::table('resource_features')->insertOrIgnore([
                    'feature_id' => $featureId,
                    'featureable_type' => Activity::class,
                    'featureable_id' => $activity->id,
                    'order' => $order++,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignmentCount++;
            }
        }

        $this->command->info("   Created {$assignmentCount} feature assignments");
    }

    /**
     * Assign users as partners/referrers to resources
     */
    private function assignUsers(array $units, array $activities): void
    {
        $tenantUsers = UserTenant::all();
        if ($tenantUsers->isEmpty()) {
            return;
        }

        $assignmentCount = 0;
        $roles = ['partner', 'referrer'];

        // Assign users to units
        foreach ($units as $unit) {
            $selectedUsers = $tenantUsers->shuffle()->take(rand(1, 2));
            foreach ($selectedUsers as $user) {
                DB::table('resource_users')->insertOrIgnore([
                    'global_id' => $user->global_id,
                    'resourceable_type' => Unit::class,
                    'resourceable_id' => $unit->id,
                    'role' => $roles[array_rand($roles)],
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignmentCount++;
            }
        }

        // Assign users to activities
        foreach ($activities as $activity) {
            $selectedUsers = $tenantUsers->shuffle()->take(rand(1, 2));
            foreach ($selectedUsers as $user) {
                DB::table('resource_users')->insertOrIgnore([
                    'global_id' => $user->global_id,
                    'resourceable_type' => Activity::class,
                    'resourceable_id' => $activity->id,
                    'role' => $roles[array_rand($roles)],
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignmentCount++;
            }
        }

        $this->command->info("   Created {$assignmentCount} user assignments");
    }

    /**
     * Create Reservations with Items
     */
    private function createReservations(array $units, array $activities): void
    {
        $faker = fake('id_ID');
        $reservationCount = rand(10, 20);
        $allResources = array_merge(
            array_map(fn($u) => ['type' => Unit::class, 'resource' => $u], $units),
            array_map(fn($a) => ['type' => Activity::class, 'resource' => $a], $activities)
        );

        if (empty($allResources)) {
            return;
        }

        $statuses = [
            Reservation::STATUS_PENDING,
            Reservation::STATUS_CONFIRMED,
            Reservation::STATUS_CONFIRMED,
            Reservation::STATUS_COMPLETED,
            Reservation::STATUS_COMPLETED,
        ];

        $itemCount = 0;

        for ($i = 0; $i < $reservationCount; $i++) {
            // Generate guest data
            $guestName = $faker->name();
            $guestEmail = $faker->safeEmail();
            $countryCode = $faker->randomElement(['+62', '+1', '+44', '+61', '+65']);
            $phoneNumber = $faker->numerify('8##########');

            // Create reservation
            $reservation = Reservation::create([
                'code' => Reservation::generateUniqueCode(),
                'guest_name' => $guestName,
                'guest_email' => $guestEmail,
                'guest_phone' => [
                    'country_code' => $countryCode,
                    'number' => $phoneNumber,
                ],
                'status' => $statuses[array_rand($statuses)],
                'currency' => 'IDR',
                'notes' => $faker->boolean(30) ? $faker->sentence() : null,
            ]);

            // Create 1-3 items per reservation
            $numItems = rand(1, 3);
            $subtotal = 0;

            for ($j = 0; $j < $numItems; $j++) {
                $resourceData = $allResources[array_rand($allResources)];
                $resource = $resourceData['resource'];
                $resourceType = $resourceData['type'];

                // Get a rate for this resource
                $rate = Rate::where('rateable_type', $resourceType)
                    ->where('rateable_id', $resource->id)
                    ->inRandomOrder()
                    ->first();

                if (!$rate) {
                    continue;
                }

                // Generate dates
                $startDate = $faker->dateTimeBetween('-1 month', '+2 months');
                $endDate = (clone $startDate)->modify('+' . rand(1, 5) . ' days');
                $quantity = rand(1, 4);

                // Calculate duration based on resource type only
                // Unit: nights = end - start
                // Activity: days = end - start + 1
                $diffDays = $startDate->diff($endDate)->days;
                $isUnit = $resourceType === Unit::class;
                $duration = $isUnit ? max(1, $diffDays) : max(1, $diffDays + 1);

                $lineTotal = $rate->price * $quantity * $duration;
                $subtotal += $lineTotal;

                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'reservable_type' => $resourceType,
                    'reservable_id' => $resource->id,
                    'rate_id' => $rate->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'start_time' => $resourceType === Activity::class ? '08:00:00' : null,
                    'end_time' => $resourceType === Activity::class ? '16:00:00' : null,
                    'quantity' => $quantity,
                    'resource_name' => $resource->name,
                    'resource_type_label' => $resourceType === Unit::class ? 'Unit' : 'Activity',
                    'resource_description' => null,
                    'rate_name' => $rate->name,
                    'rate_description' => $rate->description,
                    'price_type' => $rate->price_type,
                    'rate_price' => $rate->price,
                    'currency' => 'IDR',
                    'line_total' => $lineTotal,
                    'status' => ReservationItem::STATUS_ACTIVE,
                ]);
                $itemCount++;
            }

            // Update reservation totals
            $reservation->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);
        }

        $this->command->info("   Created {$reservationCount} reservations with {$itemCount} items");
    }
}
