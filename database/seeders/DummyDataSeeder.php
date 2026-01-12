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
                'global_id' => 'a51ea14b-138c-4214-b033-20b998c22a31',
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
        ]);

        // Set resource_routes as dynamic attribute (Stancl Tenancy stores this in 'data' JSON column)
        $tenant->resource_routes = [
            'cabins' => 'units',
            'activities' => 'activities',
        ];
        $tenant->save();

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
            $units = $this->createUnits($user);

            // 3. Create activities
            $activities = $this->createActivities($user);

            // 4. Create media for units and activities
            $this->createMedia($units, $activities);

            // 5. Create features for units and activities
            $this->createFeatures($units, $activities);

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
     * @return array<Unit>
     */
    private function createUnits(User $user): array
    {
        $createdUnits = [];
        $unitsData = [
            [
                'name' => 'Rahajeng Cabin',
                'slug' => 'rahajeng-cabin',
                'rate_price' => 450000,
                'rate_currency' => 'IDR',
                'rate_price_type' => 'night',
                'commission_type' => 'percentage',
                'commission_percentage' => 5.00,
                'commission_fixed' => null,
                'currency' => 'IDR',
                'fixed_unit' => null,
                'subtitle' => 'Entire cabin',
                'max_guests' => 4,
                'bedroom_count' => 2,
                'bathroom_count' => 1,
                'view' => 'Mountain View',
            ],
            [
                'name' => 'Rahayu Cabin',
                'slug' => 'rahayu-cabin',
                'rate_price' => 700000,
                'rate_currency' => 'IDR',
                'rate_price_type' => 'night',
                'commission_type' => 'percentage',
                'commission_percentage' => 5.00,
                'commission_fixed' => null,
                'currency' => 'IDR',
                'fixed_unit' => null,
                'subtitle' => 'Entire cabin',
                'max_guests' => 4,
                'bedroom_count' => 2,
                'bathroom_count' => 1,
                'view' => 'Lake View',
            ],
        ];

        foreach ($unitsData as $unitData) {
            // Check if unit already exists
            $existingUnit = Unit::where('slug', $unitData['slug'])->first();
            if ($existingUnit) {
                $this->command->warn("   Unit {$unitData['name']} already exists, skipping...");
                $createdUnits[] = $existingUnit;
                continue;
            }

            $unit = Unit::create([
                'name' => $unitData['name'],
                'slug' => $unitData['slug'],
                'subtitle' => $unitData['subtitle'],
                'max_guests' => $unitData['max_guests'],
                'bedroom_count' => $unitData['bedroom_count'],
                'bathroom_count' => $unitData['bathroom_count'],
                'view' => $unitData['view'],
            ]);

            // Create rate
            Rate::create([
                'rateable_type' => Unit::class,
                'rateable_id' => $unit->id,
                'name' => 'Standard Rate',
                'slug' => 'standard-rate',
                'description' => null,
                'price' => $unitData['rate_price'],
                'currency' => $unitData['rate_currency'],
                'price_type' => $unitData['rate_price_type'],
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
                'currency' => $unitData['currency'],
                'fixed_unit' => $unitData['fixed_unit'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $createdUnits[] = $unit;
        }

        $this->command->info('   Created ' . count($unitsData) . ' units with rates, resource_users, and commission configs');

        return $createdUnits;
    }

    /**
     * Create activities with rates, resource_users, and commission configs
     * @return array<Activity>
     */
    private function createActivities(User $user): array
    {
        $createdActivities = [];
        $activitiesData = [
            [
                'name' => 'Mount Batur Trekking',
                'slug' => 'mount-batur-trekking',
                'rate_price' => 350000,
                'rate_currency' => 'IDR',
                'rate_price_type' => 'person',
                'commission_type' => 'percentage',
                'commission_percentage' => 5.00,
                'commission_fixed' => null,
                'currency' => 'IDR',
                'fixed_unit' => null,
            ],
        ];

        foreach ($activitiesData as $activityData) {
            // Check if activity already exists
            $existingActivity = Activity::where('slug', $activityData['slug'])->first();
            if ($existingActivity) {
                $this->command->warn("   Activity {$activityData['name']} already exists, skipping...");
                $createdActivities[] = $existingActivity;
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
                'currency' => $activityData['rate_currency'],
                'price_type' => $activityData['rate_price_type'],
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
                'currency' => $activityData['currency'],
                'fixed_unit' => $activityData['fixed_unit'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $createdActivities[] = $activity;
        }

        $this->command->info('   Created ' . count($activitiesData) . ' activities with rates, resource_users, and commission configs');

        return $createdActivities;
    }

    /**
     * Create media records for units and activities
     * Hardcoded from Cloudflare R2 storage
     *
     * @param array<Unit> $units
     * @param array<Activity> $activities
     */
    private function createMedia(array $units, array $activities): void
    {
        // Check if media already exists
        $existingCount = DB::table('media')->count();
        if ($existingCount > 0) {
            $this->command->warn('   Media already exists, skipping...');
            return;
        }

        // Map slugs to unit/activity instances
        $unitsBySlug = collect($units)->keyBy('slug');
        $activitiesBySlug = collect($activities)->keyBy('slug');

        // Rahajeng Cabin media (5 images)
        $rahajengCabin = $unitsBySlug->get('rahajeng-cabin');
        if ($rahajengCabin) {
            $rahajengMedia = [
                [
                    'uuid' => '833522ff-5440-4e06-a1d3-eb4dd58b3bf0',
                    'collection_name' => 'images',
                    'name' => '5f03badc-c574-40ef-8975-3178184fe2af_1',
                    'file_name' => 'b4a17ec1-b2d9-4ab1-9526-f7b6094f1618.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 101652,
                    'order_column' => 1,
                ],
                [
                    'uuid' => 'd17e11b9-3d79-47de-aa41-7ae86f0b20ce',
                    'collection_name' => 'images',
                    'name' => 'b6f241dd-25ae-459e-9e9b-237b4fdaec8d',
                    'file_name' => '2fc8bcd8-c6ae-4db3-b017-9efef4e261fe.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 124170,
                    'order_column' => 2,
                ],
                [
                    'uuid' => '9ad49ba0-d1b0-439c-9869-9a5f74c30bb8',
                    'collection_name' => 'images',
                    'name' => '5ca0d662-0301-4219-a128-e55461f489f6',
                    'file_name' => '709d40f7-eab0-40eb-b988-2251b21a3566.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 84690,
                    'order_column' => 3,
                ],
                [
                    'uuid' => '05853f8b-71fa-4f38-9aef-63a4534b1c4e',
                    'collection_name' => 'images',
                    'name' => 'cdd27de4-c00a-4aed-a870-7bdce22ce90a',
                    'file_name' => 'b1b3c7f5-deb2-43dd-8afc-ee893304498e.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 101092,
                    'order_column' => 4,
                ],
                [
                    'uuid' => '0ff42a96-3447-4f17-9e39-889849b1287a',
                    'collection_name' => 'images',
                    'name' => '4b6d7bfd-7df4-40f1-bc9a-3aafa942b3fc',
                    'file_name' => '42811306-dd37-455e-ab46-d98363893330.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 70240,
                    'order_column' => 5,
                ],
            ];

            foreach ($rahajengMedia as $media) {
                DB::table('media')->insert(array_merge($media, [
                    'model_type' => Unit::class,
                    'model_id' => $rahajengCabin->id,
                    'manipulations' => '[]',
                    'custom_properties' => '[]',
                    'generated_conversions' => '[]',
                    'responsive_images' => '[]',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Rahayu Cabin media (4 images)
        $rahayuCabin = $unitsBySlug->get('rahayu-cabin');
        if ($rahayuCabin) {
            $rahayuMedia = [
                [
                    'uuid' => '71d494ec-a720-4fc8-9694-49d7121d3edc',
                    'collection_name' => 'images',
                    'name' => 'be86ef1a-d83a-4b5e-84c5-53a11e196c20',
                    'file_name' => '31da1265-3397-420f-b8f7-264c1366bfd4.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 151324,
                    'order_column' => 1,
                ],
                [
                    'uuid' => '6bf80c38-0971-4ddc-a07d-85e718d61759',
                    'collection_name' => 'images',
                    'name' => 'e1645dd5-3207-4bbf-970c-67a2ef0783f7',
                    'file_name' => 'cc176fd5-b307-473c-b45d-0d5ec045aaf3.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 165938,
                    'order_column' => 2,
                ],
                [
                    'uuid' => 'ab28574f-4c51-4daa-90de-91b011cb57e5',
                    'collection_name' => 'images',
                    'name' => '01287f27-21e1-4354-a90a-e983558fa011',
                    'file_name' => '162b7d11-92b1-4aa9-8c93-e137407a3181.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 74636,
                    'order_column' => 3,
                ],
                [
                    'uuid' => '0bc679c5-5ea9-4240-891b-99e886607ab1',
                    'collection_name' => 'images',
                    'name' => 'bf641975-7052-4c81-9eaa-77b8517d6be0',
                    'file_name' => 'f903de3c-d1fb-4332-8e95-6e07ee71e801.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 64434,
                    'order_column' => 4,
                ],
            ];

            foreach ($rahayuMedia as $media) {
                DB::table('media')->insert(array_merge($media, [
                    'model_type' => Unit::class,
                    'model_id' => $rahayuCabin->id,
                    'manipulations' => '[]',
                    'custom_properties' => '[]',
                    'generated_conversions' => '[]',
                    'responsive_images' => '[]',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Mount Batur Trekking media (1 image)
        $mountBaturTrekking = $activitiesBySlug->get('mount-batur-trekking');
        if ($mountBaturTrekking) {
            $activityMedia = [
                [
                    'uuid' => 'd37a3a1f-2f36-4969-9e49-9a2a3b4ffff4',
                    'collection_name' => 'images',
                    'name' => '209bd5bc-20d9-46ee-8a29-de8f9cbe4d08',
                    'file_name' => '2e9dca51-2f64-4f0f-93c9-55bb4bb58fda.webp',
                    'mime_type' => 'image/webp',
                    'disk' => 'r2',
                    'conversions_disk' => 'r2',
                    'size' => 90616,
                    'order_column' => 1,
                ],
            ];

            foreach ($activityMedia as $media) {
                DB::table('media')->insert(array_merge($media, [
                    'model_type' => Activity::class,
                    'model_id' => $mountBaturTrekking->id,
                    'manipulations' => '[]',
                    'custom_properties' => '[]',
                    'generated_conversions' => '[]',
                    'responsive_images' => '[]',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('   Created 10 media records for units and activities');
    }

    /**
     * Create features for units and activities
     * Based on current database data
     *
     * @param array<Unit> $units
     * @param array<Activity> $activities
     */
    private function createFeatures(array $units, array $activities): void
    {
        // ==========================================
        // UNIT FEATURES (Rahajeng & Rahayu Cabin)
        // ==========================================
        $unitFeaturesData = [
            // Amenities
            'bidet' => ['amenity', 0],
            'hot-water' => ['amenity', 1],
            'tv' => ['amenity', 2],
            'wifi' => ['amenity', 3],
            'dedicated-workspace' => ['amenity', 4],
            'kitchen' => ['amenity', 5],
            'barbecue-utensils' => ['amenity', 6],
            'dining-table' => ['amenity', 7],
            'coffee' => ['amenity', 8],
            'patio-balcony' => ['amenity', 9],
            // Facilities
            'first-aid-kit' => ['facility', 0],
            'lake-access' => ['facility', 1],
            'bbq-grill' => ['facility', 2],
            'free-parking' => ['facility', 3],
            'street-parking' => ['facility', 4],
            'pets-allowed' => ['facility', 5],
            'smoking-allowed' => ['facility', 6],
            'self-check-in' => ['facility', 7],
            'building-staff' => ['facility', 8],
            // Exclusions (not available)
            'exterior-security-cameras' => ['exclusion', 0],
            'washer' => ['exclusion', 1],
            'dryer' => ['exclusion', 2],
            'air-conditioning' => ['exclusion', 3],
            'essentials' => ['exclusion', 4],
            'smoke-alarm' => ['exclusion', 5],
            'carbon-monoxide-alarm' => ['exclusion', 6],
            'heating' => ['exclusion', 7],
        ];

        foreach ($units as $unit) {
            $this->syncFeaturesForResource($unit, Unit::class, $unitFeaturesData);
        }

        $this->command->info('   Created features for ' . count($units) . ' units');

        // ==========================================
        // ACTIVITY FEATURES (Mount Batur Trekking)
        // ==========================================
        $activityFeaturesData = [
            // Inclusions
            'hotel-transfer' => ['inclusion', 0],
            'entrance-fees' => ['inclusion', 1],
            'professional-guide' => ['inclusion', 2],
            'safety-equipment' => ['inclusion', 3],
            'breakfast' => ['inclusion', 4],
            'hot-beverages' => ['inclusion', 5],
            'mineral-water' => ['inclusion', 6],
            // Exclusions
            'travel-insurance' => ['exclusion', 0],
            'tipping' => ['exclusion', 1],
            'personal-expenses' => ['exclusion', 2],
            // Suggestions
            'hiking-shoes' => ['suggestion', 0],
            'camera' => ['suggestion', 1],
            'warm-clothing' => ['suggestion', 2],
            'sunscreen' => ['suggestion', 3],
            'cash' => ['suggestion', 4],
        ];

        foreach ($activities as $activity) {
            $this->syncFeaturesForResource($activity, Activity::class, $activityFeaturesData);
        }

        $this->command->info('   Created features for ' . count($activities) . ' activities');
    }

    /**
     * Sync features for a resource (unit or activity)
     *
     * @param mixed $resource
     * @param string $resourceClass
     * @param array $featuresData
     */
    private function syncFeaturesForResource($resource, string $resourceClass, array $featuresData): void
    {
        // Check if features already exist
        $existingCount = $resource->features()->count();
        if ($existingCount > 0) {
            $this->command->warn("   Features already exist for {$resource->name}, skipping...");
            return;
        }

        // Get central features by value
        $centralFeatures = \App\Models\Feature::whereIn('value', array_keys($featuresData))->get()->keyBy('value');

        // Upsert to resource_features table (tenant)
        $upsertData = [];
        foreach ($featuresData as $value => $data) {
            $centralFeature = $centralFeatures->get($value);
            if ($centralFeature) {
                $upsertData[] = [
                    'feature_id' => $centralFeature->id,
                    'name' => $centralFeature->name,
                    'value' => $centralFeature->value,
                    'description' => $centralFeature->description,
                    'icon' => $centralFeature->icon,
                ];
            }
        }

        if (!empty($upsertData)) {
            \App\Models\ResourceFeature::upsert(
                $upsertData,
                ['feature_id'],
                ['name', 'value', 'description', 'icon']
            );
        }

        // Attach features to resource
        $syncData = [];
        foreach ($featuresData as $value => $data) {
            $centralFeature = $centralFeatures->get($value);
            if ($centralFeature) {
                $syncData[$centralFeature->id] = [
                    'category' => $data[0],
                    'order' => $data[1],
                    'assigned_at' => now(),
                ];
            }
        }

        $resource->features()->sync($syncData);
    }
}
