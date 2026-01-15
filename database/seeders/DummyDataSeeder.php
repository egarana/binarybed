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
                'description' => "Escape to the serene highlands of Kintamani at Rahajeng Cabin, a sanctuary designed for those seeking tranquility amidst nature. Perched on the edge of the caldera, this cabin offers breathtaking panoramic views of Mount Batur and the shimmering lake below. The architecture blends traditional Balinese elements with modern comfort, featuring natural wood finishes and large glass windows that flood the space with soft, natural light, creating a warm and inviting atmosphere from the moment you step inside.

Inside, the cabin is thoughtfully appointed with cozy furnishings and premium amenities to ensure a restful stay. The spacious living area is perfect for gathering with loved ones, while the bedrooms offer plush bedding and blackout curtains for a deep, restorative sleep. A fully equipped kitchenette allows you to prepare simple meals, which you can enjoy on the private terrace as the cool mountain breeze brushes past. Whether you're sipping your morning coffee or stargazing at night, every moment here feels like a pause in time.

Beyond the cabin, adventure awaits just a stone's throw away. Explore the nearby pine forests, take a dip in the natural hot springs, or embark on a sunrise trek to the summit of Mount Batur. For those who prefer a slower pace, simply relax by the fire pit in the evening, sharing stories under a canopy of stars. Rahajeng Cabin isn't just a place to stay; it's an experience that reconnects you with the beauty of the earth and the peace within yourself.",
                'selling_points' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/></svg>', 'title' => 'Eco-Friendly', 'description' => 'Solar powered with rainwater harvesting'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>', 'title' => 'Family Owned', 'description' => 'Authentic local hospitality'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>', 'title' => 'Direct Contact', 'description' => 'Chat with host anytime, no middleman'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v10H4V12"/><path d="M22 7v5H2V7l10-5z"/></svg>', 'title' => 'Welcome Package', 'description' => 'Local snacks & handmade souvenirs'],
                ],
                'book_direct_benefits' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>', 'title' => 'Best Price Guarantee', 'description' => 'Save 10% vs OTA prices'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-coffee"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/></svg>', 'title' => 'Free Breakfast', 'description' => 'Daily local breakfast included'],
                ],
                'location' => [
                    'address' => 'Songan A, Kintamani, Bangli Regency, Bali 80652',
                    'subtitle' => '2 hours from Ngurah Rai International Airport',
                    'map_url' => 'https://www.google.com/maps/search/?api=1&query=Songan+A+Kintamani+Bangli+Bali',
                    'highlights' => [
                        '5 min walk to Lake Batur shore',
                        '10 min drive to Mount Batur trailhead',
                        'Near traditional Songan village',
                        'Surrounded by pine forest'
                    ]
                ],
                'rules' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'label' => 'Check-in: 2:00 PM - 8:00 PM'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'label' => 'Checkout: 11:00 AM'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'label' => 'Maximum 4 guests'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>', 'label' => 'No smoking inside the cabin'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>', 'label' => 'No parties or events'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/><circle cx="16.5" cy="7.5" r=".5"/></svg>', 'label' => 'Self check-in with lockbox'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10 5.172C10 3.782 8.423 2.679 6.5 3c-2.823.47-4.113 6.006-4 7 .08.703 1.725 1.722 3.656 1 1.261-.472 1.96-1.45 2.344-2.5"/><path d="M14.267 5.172c0-1.39 1.577-2.493 3.5-2.172 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5"/><path d="M8 14v.5"/><path d="M16 14v.5"/><path d="M11.25 16.25h1.5L12 17l-.75-.75Z"/><path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444c0-1.061-.162-2.2-.493-3.309m-9.243-6.082A8.801 8.801 0 0 1 12 5c.78 0 1.5.108 2.161.306"/></svg>', 'label' => 'Pets allowed with prior approval'],
                ],
                'host' => [
                    'name' => 'Made Wirawan',
                    'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face',
                    'languages' => ['English', 'Indonesian', 'Japanese'],
                    'story' => 'Born and raised in Bali, I built this cabin to share the magic of Lake Batur with travelers. Every morning, I wake up to the volcano sunrise and wanted others to experience this too.',
                    'whatsapp' => '+628123456789',
                    'instagram' => '@madewirawan_bali',
                    'facebook' => 'https://facebook.com/madewirawan',
                    'tiktok' => '@lakebaturtours',
                ],
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
                'description' => "Rahayu Cabin offers a luxurious retreat in the heart of Kintamani, where elegance meets the raw beauty of the volcanic landscape. Designed for travelers who appreciate the finer things in life, this cabin features a contemporary aesthetic with rustic touches that harmonize perfectly with its surroundings. The expansive deck provides an unrivaled vantage point to witness the majestic sunrise over Mount Abang, painting the sky in hues of orange and purple that reflect beautifully on the calm waters of Lake Batur.

Step inside to discover a haven of comfort and style. The open-plan design creates a seamless flow between the living, dining, and sleeping areas, making it ideal for families or small groups. High ceilings and floor-to-ceiling windows ensure that the stunning views are never out of sight. The bathroom is a sanctuary of its own, featuring a rain shower and organic toiletries that add a touch of spa-like indulgence to your daily routine. Every detail, from the soft lighting to the curated artwork, has been chosen to enhance your sense of well-being.

Your stay at Rahayu Cabin is more than just accommodation; it's a gateway to the cultural and natural wonders of Bali's highlands. Visit the ancient Trunyan village across the lake, explore local coffee plantations, or simply unwind in the cool mountain air. As evening falls, the cabin transforms into a cozy refuge where you can curl up with a book or enjoy a glass of wine by the heater. At Rahayu Cabin, preventing the hustle of the world is easy, allowing you to focus on what truly matters: relaxation, connection, and joy.",
                'selling_points' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/></svg>', 'title' => 'Eco-Friendly', 'description' => 'Solar powered with rainwater harvesting'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>', 'title' => 'Family Owned', 'description' => 'Authentic local hospitality'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>', 'title' => 'Direct Contact', 'description' => 'Chat with host anytime, no middleman'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v10H4V12"/><path d="M22 7v5H2V7l10-5z"/></svg>', 'title' => 'Welcome Package', 'description' => 'Local snacks & handmade souvenirs'],
                ],
                'book_direct_benefits' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>', 'title' => 'Best Price Guarantee', 'description' => 'Save 10% vs OTA prices'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-coffee"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/></svg>', 'title' => 'Free Breakfast', 'description' => 'Daily local breakfast included'],
                ],
                'location' => [
                    'address' => 'Kedisan Village, Kintamani, Bangli Regency, Bali 80652',
                    'subtitle' => '1.5 hours from Ubud',
                    'map_url' => 'https://www.google.com/maps/place/Kedisan+Village+Kintamani/@-8.2425,115.3892,14z',
                    'highlights' => [
                        'Panoramic Lake Batur view',
                        '15 min to Toya Bungkah hot springs',
                        'Quiet lakeside location'
                    ]
                ],
                'rules' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'label' => 'Check-in: 2:00 PM - 8:00 PM'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'label' => 'Checkout: 11:00 AM'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'label' => 'Maximum 4 guests'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>', 'label' => 'No smoking inside the cabin'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>', 'label' => 'No pets allowed'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M2 5h20"/><path d="M3 3v2"/><path d="M7 3v2"/><path d="M17 3v2"/><path d="M21 3v2"/></svg>', 'label' => 'Quiet hours after 10:00 PM'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/><circle cx="16.5" cy="7.5" r=".5"/></svg>', 'label' => 'Self check-in with smart lock'],
                ],
                'host' => [
                    'name' => 'Wayan Sari',
                    'photo' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face',
                    'languages' => ['English', 'Indonesian'],
                    'story' => 'As a lifelong resident of Kintamani, I have always been passionate about sharing our beautiful highlands with visitors from around the world. I take pride in offering warm hospitality and ensuring every guest feels at home.',
                    'whatsapp' => '+628987654321',
                    'instagram' => '@wayansari_kintamani',
                    'facebook' => 'https://facebook.com/wayansari.kintamani',
                ],
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
                'description' => $unitData['description'],
                'subtitle' => $unitData['subtitle'],
                'max_guests' => $unitData['max_guests'],
                'bedroom_count' => $unitData['bedroom_count'],
                'bathroom_count' => $unitData['bathroom_count'],
                'view' => $unitData['view'],
                'selling_points' => $unitData['selling_points'],
                'book_direct_benefits' => $unitData['book_direct_benefits'] ?? null,
                'location' => $unitData['location'] ?? null,
                'rules' => $unitData['rules'] ?? null,
                'host' => $unitData['host'] ?? null,
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
                'subtitle' => 'Guided Adventure',
                'description' => "Embark on an unforgettable journey to the summit of Mount Batur, one of Bali's most iconic active volcanoes. This guided trekking experience is designed for adventurers of all levels, offering a chance to witness the island from above the clouds. Starting in the cool, pre-dawn hours, you'll hike up the volcanic slopes under a blanket of stars, guided by experienced locals who know every turn of the trail. The crisp mountain air and the anticipation of the sunrise create an atmosphere of excitement and camaraderie among fellow trekkers.

As you reach the summit, you'll be rewarded with a spectacle that defies description—the sunrise over the caldera. Watch as the sun slowly emerges from the horizon, illuminating the vast crater lake and the distant peaks of Mount Abang and Mount Agung in a golden glow. It's a moment of pure magic, perfect for photography or quiet reflection. To make the experience even more special, your guides will prepare a simple, delicious breakfast cooked using the natural steam vents of the volcano, a unique culinary experience you won't find anywhere else.

The descent offers its own set of wonders, with sweeping views of the lava fields and the lush agricultural land surrounding the mountain. You'll pass through villages and farms, getting a glimpse of local life in the highlands. Whether you're an avid hiker or a first-time trekker, the Mount Batur Sunrise Trek is a bucket-list adventure that combines physical challenge with spiritual awe. It's not just a hike; it's a journey to the top of the world, leaving you with memories that will last a lifetime.",
                'highlights' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'label' => '4-5 hours'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'label' => 'Max 8 people'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>', 'label' => 'Hotel pickup'],
                ],
                'selling_points' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>', 'title' => 'Expert Guide', 'description' => 'Certified local guides with 5+ years experience'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'title' => 'Small Groups', 'description' => 'Maximum 8 people for personalized attention'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>', 'title' => 'Safety First', 'description' => 'All equipment provided & regularly inspected'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'title' => 'Photo Included', 'description' => 'Professional photos of your adventure'],
                ],
                'book_direct_benefits' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>', 'title' => 'Best Price Guarantee', 'description' => 'Save 10% vs OTA prices'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-coffee"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/></svg>', 'title' => 'Free Breakfast', 'description' => 'Daily local breakfast included'],
                ],
                'location' => [
                    'address' => 'Mount Batur Trailhead, Toya Bungkah, Kintamani, Bangli 80652',
                    'subtitle' => '2 hours from Ubud, pickup available',
                    'map_url' => 'https://www.google.com/maps/place/Mount+Batur/@-8.242,115.375,13z',
                    'highlights' => [
                        'Starting point at 1,200m elevation',
                        'Summit at 1,717m above sea level',
                        'Crater lake viewpoint',
                        'Natural steam vents',
                        'Sunrise ceremony site'
                    ]
                ],
                'rules' => [
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'label' => 'Pickup time: 2:00 AM - 3:00 AM'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'label' => 'Minimum age: 10 years'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>', 'label' => 'Basic fitness level required'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>', 'label' => 'Not suitable for pregnant women'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>', 'label' => 'No sandals or flip-flops'],
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>', 'label' => 'Full refund if cancelled 24h before'],
                ],
                'host' => [
                    'name' => 'Ketut Bawa',
                    'photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face',
                    'languages' => ['English', 'Indonesian', 'Mandarin'],
                    'story' => 'I have been guiding Mount Batur treks for over 15 years. Nothing compares to sharing the magic of sunrise from the summit with fellow adventurers. Safety and unforgettable experiences are my top priorities.',
                    'whatsapp' => '+628234567890',
                    'instagram' => '@ketutbawa_guide',
                    'facebook' => 'https://facebook.com/ketutbawa.mountbatur',
                    'tiktok' => '@mountbaturguide',
                ],
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
                'subtitle' => $activityData['subtitle'],
                'description' => $activityData['description'],
                'highlights' => $activityData['highlights'],
                'selling_points' => $activityData['selling_points'],
                'book_direct_benefits' => $activityData['book_direct_benefits'] ?? null,
                'location' => $activityData['location'] ?? null,
                'rules' => $activityData['rules'] ?? null,
                'host' => $activityData['host'] ?? null,
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
