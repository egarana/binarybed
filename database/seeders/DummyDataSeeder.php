<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Feature;
use App\Models\Rate;
use App\Models\ResourceFeature;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserTenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Users
        echo "🌱 Seeding 30 users...\n";
        $this->seedUsers();

        // Seed Tenants
        echo "🏢 Seeding 10 tenants...\n";
        $this->seedTenants();

        echo "✅ Dummy data seeding completed!\n";
    }

    /**
     * ==========================================
     * USER SEEDING - HARD CODED (30 users)
     * ==========================================
     */
    private function seedUsers(): void
    {
        $users = [
            // 50% Balinese names (15 users)
            ['name' => 'I Wayan Sukarta', 'email' => 'wayan.sukarta@example.com'],
            ['name' => 'Ni Luh Sari', 'email' => 'luh.sari@example.com'],
            ['name' => 'I Made Agus', 'email' => 'made.agus@example.com'],
            ['name' => 'Ni Made Dewi', 'email' => 'made.dewi@example.com'],
            ['name' => 'I Nyoman Putra', 'email' => 'nyoman.putra@example.com'],
            ['name' => 'Ni Nyoman Ayu', 'email' => 'nyoman.ayu@example.com'],
            ['name' => 'I Ketut Rai', 'email' => 'ketut.rai@example.com'],
            ['name' => 'Ni Ketut Kadek', 'email' => 'ketut.kadek@example.com'],
            ['name' => 'I Wayan Sudana', 'email' => 'wayan.sudana@example.com'],
            ['name' => 'Ni Luh Wulandari', 'email' => 'luh.wulandari@example.com'],
            ['name' => 'I Made Gede', 'email' => 'made.gede@example.com'],
            ['name' => 'Ni Made Puspita', 'email' => 'made.puspita@example.com'],
            ['name' => 'I Nyoman Widana', 'email' => 'nyoman.widana@example.com'],
            ['name' => 'Ni Nyoman Indah', 'email' => 'nyoman.indah@example.com'],
            ['name' => 'I Ketut Suryawan', 'email' => 'ketut.suryawan@example.com'],

            // 25% Indonesian names (8 users)
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@example.com'],
            ['name' => 'Siti Rahayu', 'email' => 'siti.rahayu@example.com'],
            ['name' => 'Ahmad Pratama', 'email' => 'ahmad.pratama@example.com'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@example.com'],
            ['name' => 'Rizki Wijaya', 'email' => 'rizki.wijaya@example.com'],
            ['name' => 'Maya Permata', 'email' => 'maya.permata@example.com'],
            ['name' => 'Andi Kusuma', 'email' => 'andi.kusuma@example.com'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@example.com'],

            // 25% International names (7 users)
            ['name' => 'John Smith', 'email' => 'john.smith@example.com'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@example.com'],
            ['name' => 'Michael Brown', 'email' => 'michael.brown@example.com'],
            ['name' => 'Emily Davis', 'email' => 'emily.davis@example.com'],
            ['name' => 'David Wilson', 'email' => 'david.wilson@example.com'],
            ['name' => 'Lisa Anderson', 'email' => 'lisa.anderson@example.com'],
            ['name' => 'James Taylor', 'email' => 'james.taylor@example.com'],
        ];

        foreach ($users as $userData) {
            User::create([
                'global_id' => Str::uuid()->toString(),
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
            ]);
        }
    }

    /**
     * ==========================================
     * TENANT SEEDING - HARD CODED (10 tenants)
     * 5 with accommodations + activities
     * 5 with activities only
     * ==========================================
     */
    private function seedTenants(): void
    {
        $tenants = [
            // 5 tenants with both ACCOMMODATIONS (units) and ACTIVITIES
            [
                'id' => 'karmabeachresort',
                'name' => 'Karma Beach Resort',
                'domain' => 'karmabeachresort.test',
                'resource_routes' => [
                    'accommodations' => 'units',
                    'experiences' => 'activities',
                ],
                'units' => [
                    ['name' => 'Ocean View Villa', 'slug' => 'ocean-view-villa'],
                    ['name' => 'Garden Pool Villa', 'slug' => 'garden-pool-villa'],
                    ['name' => 'Deluxe Suite', 'slug' => 'deluxe-suite'],
                    ['name' => 'Beach Front Villa', 'slug' => 'beach-front-villa'],
                    ['name' => 'Lotus Conference Hall', 'slug' => 'lotus-conference-hall'],
                    ['name' => 'Serenity Spa Room', 'slug' => 'serenity-spa-room'],
                ],
                'activities' => [
                    ['name' => 'Balinese Massage', 'slug' => 'balinese-massage'],
                    ['name' => 'Surfing Lessons', 'slug' => 'surfing-lessons'],
                    ['name' => 'Yoga Class', 'slug' => 'yoga-class'],
                    ['name' => 'Sunset Dinner Cruise', 'slug' => 'sunset-dinner-cruise'],
                    ['name' => 'Snorkeling Trip', 'slug' => 'snorkeling-trip'],
                ],
            ],
            [
                'id' => 'sanurparadisehotel',
                'name' => 'Sanur Paradise Hotel',
                'domain' => 'sanurparadisehotel.test',
                'resource_routes' => [
                    'rooms' => 'units',
                    'experiences' => 'activities',
                ],
                'units' => [
                    ['name' => 'Superior Room', 'slug' => 'superior-room'],
                    ['name' => 'Executive Room', 'slug' => 'executive-room'],
                    ['name' => 'Deluxe Suite', 'slug' => 'deluxe-suite'],
                    ['name' => 'Presidential Suite', 'slug' => 'presidential-suite'],
                    ['name' => 'Frangipani Meeting Room', 'slug' => 'frangipani-meeting-room'],
                ],
                'activities' => [
                    ['name' => 'Traditional Dance Show', 'slug' => 'traditional-dance-show'],
                    ['name' => 'Hot Stone Therapy', 'slug' => 'hot-stone-therapy'],
                    ['name' => 'Cooking Class', 'slug' => 'cooking-class'],
                    ['name' => 'Temple Tour', 'slug' => 'temple-tour'],
                ],
            ],
            [
                'id' => 'seminyakvillaretreat',
                'name' => 'Seminyak Villa Retreat',
                'domain' => 'seminyakvillaretreat.test',
                'resource_routes' => [
                    'villas' => 'units',
                    'experiences' => 'activities',
                ],
                'units' => [
                    ['name' => 'Tropical Villa', 'slug' => 'tropical-villa'],
                    ['name' => 'Sunset Villa', 'slug' => 'sunset-villa'],
                    ['name' => 'Private Garden Villa', 'slug' => 'private-garden-villa'],
                    ['name' => 'Clifftop Villa', 'slug' => 'clifftop-villa'],
                    ['name' => 'Harmony Spa Suite', 'slug' => 'harmony-spa-suite'],
                ],
                'activities' => [
                    ['name' => 'Couples Spa Package', 'slug' => 'couples-spa-package'],
                    ['name' => 'Private Beach Dining', 'slug' => 'private-beach-dining'],
                    ['name' => 'Romantic Photoshoot', 'slug' => 'romantic-photoshoot'],
                    ['name' => 'Sunset Cocktail Ceremony', 'slug' => 'sunset-cocktail-ceremony'],
                    ['name' => 'Beach Bonfire Night', 'slug' => 'beach-bonfire-night'],
                ],
            ],
            [
                'id' => 'ubudgardenspa',
                'name' => 'Ubud Garden Spa',
                'domain' => 'ubudgardenspa.test',
                'resource_routes' => [
                    'spa-rooms' => 'units',
                    'treatments' => 'activities',
                ],
                'units' => [
                    ['name' => 'Zen Therapy Room', 'slug' => 'zen-therapy-room'],
                    ['name' => 'Tranquil Massage Room', 'slug' => 'tranquil-massage-room'],
                    ['name' => 'Lotus Spa Chamber', 'slug' => 'lotus-spa-chamber'],
                    ['name' => 'Balance Spa Room', 'slug' => 'balance-spa-room'],
                    ['name' => 'Garden Spa Pavilion', 'slug' => 'garden-spa-pavilion'],
                    ['name' => 'Wooden Bungalow', 'slug' => 'wooden-bungalow'],
                ],
                'activities' => [
                    ['name' => 'Aromatherapy Session', 'slug' => 'aromatherapy-session'],
                    ['name' => 'Meditation Session', 'slug' => 'meditation-session'],
                    ['name' => 'Reflexology Foot Massage', 'slug' => 'reflexology-foot-massage'],
                    ['name' => 'Detox Body Wrap', 'slug' => 'detox-body-wrap'],
                    ['name' => 'Rice Terrace Visit', 'slug' => 'rice-terrace-visit'],
                    ['name' => 'Tai Chi Session', 'slug' => 'tai-chi-session'],
                ],
            ],
            [
                'id' => 'nusaduahospitality',
                'name' => 'Nusa Dua Hospitality',
                'domain' => 'nusaduahospitality.test',
                'resource_routes' => [
                    'venues' => 'units',
                    'services' => 'activities',
                ],
                'units' => [
                    ['name' => 'Deluxe Pool Villa', 'slug' => 'deluxe-pool-villa'],
                    ['name' => 'Sea Breeze Villa', 'slug' => 'sea-breeze-villa'],
                    ['name' => 'Junior Suite', 'slug' => 'junior-suite'],
                    ['name' => 'Penthouse Suite', 'slug' => 'penthouse-suite'],
                    ['name' => 'Orchid Boardroom', 'slug' => 'orchid-boardroom'],
                    ['name' => 'Grand Ballroom', 'slug' => 'grand-ballroom'],
                    ['name' => 'Nirvana Treatment Suite', 'slug' => 'nirvana-treatment-suite'],
                ],
                'activities' => [
                    ['name' => 'Facial Treatment', 'slug' => 'facial-treatment'],
                    ['name' => 'Wine Tasting Session', 'slug' => 'wine-tasting-session'],
                    ['name' => 'Chef Table Experience', 'slug' => 'chef-table-experience'],
                    ['name' => 'Parasailing Adventure', 'slug' => 'parasailing-adventure'],
                    ['name' => 'Jet Ski Rental', 'slug' => 'jet-ski-rental'],
                    ['name' => 'Live Music Night', 'slug' => 'live-music-night'],
                ],
            ],

            // 5 tenants with ACTIVITIES ONLY (no units/accommodations)
            [
                'id' => 'balihaidiving',
                'name' => 'Bali Hai Diving Center',
                'domain' => 'balihaidiving.test',
                'resource_routes' => [
                    'dive-courses' => 'activities',
                ],
                'units' => [],
                'activities' => [
                    ['name' => 'Scuba Diving', 'slug' => 'scuba-diving'],
                    ['name' => 'Deep Sea Fishing', 'slug' => 'deep-sea-fishing'],
                    ['name' => 'Glass Bottom Boat Tour', 'slug' => 'glass-bottom-boat-tour'],
                    ['name' => 'Snorkeling Trip', 'slug' => 'snorkeling-trip'],
                    ['name' => 'Kayaking Tour', 'slug' => 'kayaking-tour'],
                    ['name' => 'Underwater Photography', 'slug' => 'underwater-photography'],
                ],
            ],
            [
                'id' => 'canggusurfschool',
                'name' => 'Canggu Surf School',
                'domain' => 'canggusurfschool.test',
                'resource_routes' => [
                    'lessons' => 'activities',
                ],
                'units' => [],
                'activities' => [
                    ['name' => 'Beginner Surfing Lessons', 'slug' => 'beginner-surfing-lessons'],
                    ['name' => 'Advanced Surfing Lessons', 'slug' => 'advanced-surfing-lessons'],
                    ['name' => 'Stand-Up Paddleboarding', 'slug' => 'stand-up-paddleboarding'],
                    ['name' => 'Beach Bootcamp', 'slug' => 'beach-bootcamp'],
                    ['name' => 'Surf Photography Package', 'slug' => 'surf-photography-package'],
                ],
            ],
            [
                'id' => 'baliadvtours',
                'name' => 'Bali Adventure Tours',
                'domain' => 'baliadvtours.test',
                'resource_routes' => [
                    'tours' => 'activities',
                ],
                'units' => [],
                'activities' => [
                    ['name' => 'ATV Riding Adventure', 'slug' => 'atv-riding-adventure'],
                    ['name' => 'White Water Rafting', 'slug' => 'white-water-rafting'],
                    ['name' => 'Jungle Trekking', 'slug' => 'jungle-trekking'],
                    ['name' => 'Waterfall Exploration', 'slug' => 'waterfall-exploration'],
                    ['name' => 'Mount Batur Sunrise Trek', 'slug' => 'mount-batur-sunrise-trek'],
                    ['name' => 'Rice Terrace Cycling', 'slug' => 'rice-terrace-cycling'],
                ],
            ],
            [
                'id' => 'ubudculturalexp',
                'name' => 'Ubud Cultural Experiences',
                'domain' => 'ubudculturalexp.test',
                'resource_routes' => [
                    'classes' => 'activities',
                ],
                'units' => [],
                'activities' => [
                    ['name' => 'Balinese Cooking Class', 'slug' => 'balinese-cooking-class'],
                    ['name' => 'Traditional Dance Workshop', 'slug' => 'traditional-dance-workshop'],
                    ['name' => 'Batik Making Class', 'slug' => 'batik-making-class'],
                    ['name' => 'Wood Carving Workshop', 'slug' => 'wood-carving-workshop'],
                    ['name' => 'Temple Ceremony Tour', 'slug' => 'temple-ceremony-tour'],
                    ['name' => 'Silver Jewelry Making', 'slug' => 'silver-jewelry-making'],
                ],
            ],
            [
                'id' => 'baliwellness',
                'name' => 'Bali Wellness Hub',
                'domain' => 'baliwellness.test',
                'resource_routes' => [
                    'programs' => 'activities',
                ],
                'units' => [],
                'activities' => [
                    ['name' => 'Yoga & Meditation Retreat', 'slug' => 'yoga-meditation-retreat'],
                    ['name' => 'Sound Healing Session', 'slug' => 'sound-healing-session'],
                    ['name' => 'Reiki Energy Healing', 'slug' => 'reiki-energy-healing'],
                    ['name' => 'Breathwork Workshop', 'slug' => 'breathwork-workshop'],
                    ['name' => 'Holistic Wellness Consultation', 'slug' => 'holistic-wellness-consultation'],
                    ['name' => 'Detox Program', 'slug' => 'detox-program'],
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create tenant with basic info first
            $tenant = Tenant::create([
                'id' => $tenantData['id'],
                'name' => $tenantData['name'],
            ]);

            // Set custom attributes using Stancl Tenancy's dynamic attribute style
            // These are stored in the 'data' JSON column automatically
            $tenant->resource_routes = $tenantData['resource_routes'] ?? [];
            $tenant->save();

            // Create domain
            $tenant->domains()->create([
                'domain' => $tenantData['domain'],
            ]);

            // Seed units and activities in tenant context
            tenancy()->initialize($tenant);

            // Sync all features from central to tenant database
            $this->syncFeaturesToTenant();

            foreach ($tenantData['units'] as $unitData) {
                $unit = Unit::create([
                    'name' => $unitData['name'],
                    'slug' => $unitData['slug'],
                ]);

                // Create 1-5 rates for this unit with realistic names
                $this->seedRatesForUnit($unit);

                // Randomly attach 0-3 users to this unit
                $this->attachRandomUsersToUnit($unit);

                // Attach 12-25 features to this unit
                $this->attachFeaturesToUnit($unit);
            }

            // Seed activities from hardcoded tenant data
            foreach ($tenantData['activities'] as $activityData) {
                $activity = Activity::create([
                    'name' => $activityData['name'],
                    'slug' => $activityData['slug'],
                ]);

                // Create 1-5 rates for this activity with realistic names
                $this->seedRatesForActivity($activity);

                // Randomly attach 0-3 users to this activity
                $this->attachRandomUsersToActivity($activity);

                // Attach 12-25 features to this activity
                $this->attachFeaturesToActivity($activity);
            }

            tenancy()->end();

            echo "  ✓ Created tenant: {$tenant->name} with " . count($tenantData['units']) . " units and " . count($tenantData['activities']) . " activities\n";
        }
    }


    /**
     * Seed rates for a unit with realistic pricing
     */
    private function seedRatesForUnit(Unit $unit): void
    {
        // Hardcoded rates - randomly choose one set
        $rateSets = [
            // Set 1: Standard + Weekend + Peak Season
            [
                ['name' => 'Standard Rate', 'price' => 850000, 'description' => 'Our regular pricing for all year round bookings'],
                ['name' => 'Weekend Rate', 'price' => 1200000, 'description' => 'Special pricing for Friday, Saturday and Sunday'],
                ['name' => 'Peak Season Rate', 'price' => 2500000, 'description' => 'High season pricing (July-August, December-January)'],
            ],
            // Set 2: Early Bird + Last Minute + Holiday
            [
                ['name' => 'Early Bird Rate', 'price' => 750000, 'description' => 'Book 30+ days in advance and save'],
                ['name' => 'Last Minute Rate', 'price' => 950000, 'description' => 'Limited availability for last minute bookings'],
                ['name' => 'Holiday Rate', 'price' => 2000000, 'description' => 'Premium pricing for public holidays and special occasions'],
            ],
            // Set 3: Standard + Long Stay + Corporate
            [
                ['name' => 'Standard Rate', 'price' => 900000, 'description' => 'Our regular pricing for all year round bookings'],
                ['name' => 'Long Stay Rate (7+ nights)', 'price' => 650000, 'description' => 'Discounted rate for extended stays'],
                ['name' => 'Corporate Rate', 'price' => 1100000, 'description' => 'Special pricing for business travelers and corporate accounts'],
                ['name' => 'Monthly Rate', 'price' => 550000, 'description' => 'Best value for monthly bookings'],
            ],
            // Set 4: Low Season + Midweek + Honeymoon
            [
                ['name' => 'Low Season Rate', 'price' => 600000, 'description' => 'Best value pricing during quiet months'],
                ['name' => 'Midweek Special', 'price' => 700000, 'description' => 'Great value for Monday to Thursday stays'],
                ['name' => 'Honeymoon Package', 'price' => 3000000, 'description' => 'Romantic package with special inclusions'],
            ],
        ];

        // Randomly pick one rate set
        $selectedSet = $rateSets[array_rand($rateSets)];

        // Create rates from selected set
        foreach ($selectedSet as $rateData) {
            $unit->rates()->create([
                'name' => $rateData['name'],
                'slug' => Str::slug($rateData['name']),
                'description' => $rateData['description'],
                'price' => $rateData['price'],
                'currency' => 'IDR',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Seed rates for an activity with realistic pricing
     */
    private function seedRatesForActivity(Activity $activity): void
    {
        // Hardcoded rates - randomly choose one set
        $rateSets = [
            // Set 1: Standard + Group + Private
            [
                ['name' => 'Standard Rate', 'price' => 300000, 'description' => 'Our regular pricing for all year round bookings'],
                ['name' => 'Group Rate (4+ pax)', 'price' => 250000, 'description' => 'Discounted rate for groups of 4 or more'],
                ['name' => 'Private Session', 'price' => 800000, 'description' => 'Exclusive one-on-one experience'],
            ],
            // Set 2: Couple + Family + Full Day
            [
                ['name' => 'Couple Package', 'price' => 550000, 'description' => 'Perfect for two people'],
                ['name' => 'Family Package (4 pax)', 'price' => 900000, 'description' => 'Great value for families up to 4 people'],
                ['name' => 'Full Day Package', 'price' => 1500000, 'description' => 'Complete day experience with all inclusions'],
            ],
            // Set 3: Peak Hours + Off-Peak + Weekend
            [
                ['name' => 'Peak Hours Rate', 'price' => 400000, 'description' => 'Premium time slots with high demand'],
                ['name' => 'Off-Peak Rate', 'price' => 200000, 'description' => 'Best value during quieter times'],
                ['name' => 'Weekend Rate', 'price' => 350000, 'description' => 'Special pricing for Friday, Saturday and Sunday'],
                ['name' => 'Early Morning Rate', 'price' => 250000, 'description' => 'Special pricing for morning sessions'],
            ],
            // Set 4: Sunset + Half Day
            [
                ['name' => 'Sunset Session', 'price' => 500000, 'description' => 'Experience during the golden hour'],
                ['name' => 'Half Day Package', 'price' => 650000, 'description' => 'Perfect half-day experience'],
            ],
        ];

        // Randomly pick one rate set
        $selectedSet = $rateSets[array_rand($rateSets)];

        // Create rates from selected set
        foreach ($selectedSet as $rateData) {
            $activity->rates()->create([
                'name' => $rateData['name'],
                'slug' => Str::slug($rateData['name']),
                'description' => $rateData['description'],
                'price' => $rateData['price'],
                'currency' => 'IDR',
                'is_active' => true,
            ]);
        }
    }



    /**
     * Attach users to a unit with hardcoded assignments
     * Real world: Not all units have assigned staff, some have partners/referrers
     */
    private function attachRandomUsersToUnit(Unit $unit): void
    {
        // Hardcoded user assignment sets (sometimes no users, sometimes 1-3)
        $userSets = [
            // No users assigned (50% of cases)
            [],
            [],
            [],
            [],
            [],
            // Single partner
            [
                ['email' => 'wayan.sukarta@example.com', 'role' => 'partner', 'days_ago' => 30],
            ],
            // Single referrer
            [
                ['email' => 'luh.sari@example.com', 'role' => 'referrer', 'days_ago' => 45],
            ],
            // Two users: partner + referrer
            [
                ['email' => 'made.agus@example.com', 'role' => 'partner', 'days_ago' => 15],
                ['email' => 'budi.santoso@example.com', 'role' => 'referrer', 'days_ago' => 60],
            ],
            // Three users: 2 partners + 1 referrer
            [
                ['email' => 'nyoman.putra@example.com', 'role' => 'partner', 'days_ago' => 20],
                ['email' => 'john.smith@example.com', 'role' => 'partner', 'days_ago' => 35],
                ['email' => 'ahmad.pratama@example.com', 'role' => 'referrer', 'days_ago' => 50],
            ],
            // Two partners
            [
                ['email' => 'ketut.rai@example.com', 'role' => 'partner', 'days_ago' => 10],
                ['email' => 'dewi.lestari@example.com', 'role' => 'partner', 'days_ago' => 25],
            ],
        ];

        // Randomly pick one set
        $selectedSet = $userSets[array_rand($userSets)];

        // If empty set, no users assigned
        if (empty($selectedSet)) {
            return;
        }

        // Attach users from selected set
        foreach ($selectedSet as $userData) {
            // Get user from central database by email
            $centralUser = User::where('email', $userData['email'])->first();

            if (!$centralUser) {
                continue; // Skip if user not found
            }

            // Sync user to tenant database first
            $tenantUser = UserTenant::firstOrCreate(
                ['global_id' => $centralUser->global_id],
                [
                    'name' => $centralUser->name,
                    'email' => $centralUser->email,
                ]
            );

            // Attach user to unit with role
            $unit->users()->syncWithoutDetaching([
                $tenantUser->global_id => [
                    'role' => $userData['role'],
                    'assigned_at' => now()->subDays($userData['days_ago']),
                ]
            ]);
        }
    }

    /**
     * Attach users to an activity with hardcoded assignments
     * Real world: Activities often have instructors/guides (partners) or referrers
     */
    private function attachRandomUsersToActivity(Activity $activity): void
    {
        // Hardcoded user assignment sets (sometimes no users, sometimes 1-3)
        $userSets = [
            // No users assigned (40% of cases)
            [],
            [],
            [],
            [],
            // Single partner (instructor/guide)
            [
                ['email' => 'made.dewi@example.com', 'role' => 'partner', 'days_ago' => 20],
            ],
            // Single partner
            [
                ['email' => 'nyoman.ayu@example.com', 'role' => 'partner', 'days_ago' => 35],
            ],
            // Two partners (main + assistant instructor)
            [
                ['email' => 'ketut.kadek@example.com', 'role' => 'partner', 'days_ago' => 10],
                ['email' => 'wayan.sudana@example.com', 'role' => 'partner', 'days_ago' => 15],
            ],
            // Partner + referrer
            [
                ['email' => 'luh.wulandari@example.com', 'role' => 'partner', 'days_ago' => 25],
                ['email' => 'rizki.wijaya@example.com', 'role' => 'referrer', 'days_ago' => 40],
            ],
            // Three users: 2 partners + 1 referrer
            [
                ['email' => 'made.gede@example.com', 'role' => 'partner', 'days_ago' => 5],
                ['email' => 'sarah.johnson@example.com', 'role' => 'partner', 'days_ago' => 30],
                ['email' => 'andi.kusuma@example.com', 'role' => 'referrer', 'days_ago' => 45],
            ],
            // Single referrer
            [
                ['email' => 'michael.brown@example.com', 'role' => 'referrer', 'days_ago' => 55],
            ],
        ];

        // Randomly pick one set
        $selectedSet = $userSets[array_rand($userSets)];

        // If empty set, no users assigned
        if (empty($selectedSet)) {
            return;
        }

        // Attach users from selected set
        foreach ($selectedSet as $userData) {
            // Get user from central database by email
            $centralUser = User::where('email', $userData['email'])->first();

            if (!$centralUser) {
                continue; // Skip if user not found
            }

            // Sync user to tenant database first
            $tenantUser = UserTenant::firstOrCreate(
                ['global_id' => $centralUser->global_id],
                [
                    'name' => $centralUser->name,
                    'email' => $centralUser->email,
                ]
            );

            // Attach user to activity with role
            $activity->users()->syncWithoutDetaching([
                $tenantUser->global_id => [
                    'role' => $userData['role'],
                    'assigned_at' => now()->subDays($userData['days_ago']),
                ]
            ]);
        }
    }

    /**
     * Sync all features from central database to tenant database
     * Features must exist in tenant DB before they can be attached to resources
     */
    private function syncFeaturesToTenant(): void
    {
        // Get all features from central database
        $centralFeatures = Feature::all();

        foreach ($centralFeatures as $feature) {
            // Create or update feature in tenant database
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
    }

    /**
     * Attach 12-25 hardcoded features to a unit
     * Different feature sets for luxury villas, standard rooms, and budget accommodations
     */
    private function attachFeaturesToUnit(Unit $unit): void
    {
        // Hardcoded feature sets for different unit types (by feature value)
        $featureSets = [
            // Set 1: Luxury Villa (25 features) - All amenities + facilities + inclusions
            [
                'free_wifi',
                'kitchen',
                'tv',
                'hot_water',
                'dedicated_workspace',
                'private_bathroom',
                'bidet',
                'dining_table',
                'non_smoking_rooms',
                'patio_or_balcony',
                'free_parking',
                '24_7_reception',
                'garden',
                'picnic_area',
                'outdoor_furniture',
                'terrace',
                'lake_access',
                'first_aid_kit',
                'breakfast',
                'coffee',
                'fruit',
                'daily_housekeeping',
                'room_service',
                'airport_shuttle',
                'self_check_in'
            ],
            // Set 2: Deluxe Room (20 features) - Most amenities + some facilities + inclusions
            [
                'free_wifi',
                'kitchen',
                'tv',
                'hot_water',
                'dedicated_workspace',
                'private_bathroom',
                'dining_table',
                'non_smoking_rooms',
                'patio_or_balcony',
                'free_parking',
                '24_7_reception',
                'garden',
                'outdoor_furniture',
                'breakfast',
                'coffee',
                'fruit',
                'daily_housekeeping',
                'room_service',
                'ironing_service',
                'self_check_in'
            ],
            // Set 3: Standard Room (16 features) - Basic amenities + some facilities
            [
                'free_wifi',
                'tv',
                'hot_water',
                'private_bathroom',
                'bidet',
                'dining_table',
                'non_smoking_rooms',
                'free_parking',
                '24_7_reception',
                'garden',
                'breakfast',
                'coffee',
                'daily_housekeeping',
                'ironing_service',
                'self_check_in',
                'first_aid_kit'
            ],
            // Set 4: Budget Accommodation (12 features) - Essential amenities only
            [
                'free_wifi',
                'tv',
                'hot_water',
                'private_bathroom',
                'non_smoking_rooms',
                'free_parking',
                'breakfast',
                'coffee',
                'first_aid_kit',
                'self_check_in',
                'lake_access',
                'picnic_area'
            ],
            // Set 5: Spa Suite (18 features) - Wellness focused
            [
                'free_wifi',
                'tv',
                'hot_water',
                'dedicated_workspace',
                'private_bathroom',
                'bidet',
                'dining_table',
                'non_smoking_rooms',
                'patio_or_balcony',
                'garden',
                'terrace',
                'fruit',
                'daily_housekeeping',
                'room_service',
                'ironing_service',
                'coffee',
                'breakfast',
                'lake_access'
            ],
        ];

        // Randomly pick one feature set
        $selectedFeatureValues = $featureSets[array_rand($featureSets)];

        // Attach features with order
        $order = 1;
        foreach ($selectedFeatureValues as $featureValue) {
            // Find feature in tenant database by value
            $feature = ResourceFeature::where('value', $featureValue)->first();

            if ($feature) {
                // Attach to unit via resource_features pivot table
                $unit->features()->syncWithoutDetaching([
                    $feature->feature_id => [
                        'order' => $order++,
                        'assigned_at' => now()->subDays(rand(1, 30)),
                    ]
                ]);
            }
        }
    }

    /**
     * Attach 12-25 hardcoded features to an activity
     * Different feature sets for adventure, wellness, cultural activities
     */
    private function attachFeaturesToActivity(Activity $activity): void
    {
        // Hardcoded feature sets for different activity types (by feature value)
        $featureSets = [
            // Set 1: Adventure Activity (20 features) - Equipment + inclusions + requirements
            [
                'bicycle_rental',
                'bbq_utensils',
                'car_rental',
                'breakfast',
                'coffee',
                'fruit',
                'airport_shuttle',
                'cultural_tours',
                'self_check_in',
                'free_parking',
                'first_aid_kit',
                'garden',
                'outdoor_furniture',
                'picnic_area',
                'cycling_off_site',
                'hiking_off_site',
                'fishing_off_site',
                'bike_tours',
                'lake_access',
                'pets_allowed'
            ],
            // Set 2: Wellness/Spa Activity (18 features) - Relaxation focused
            [
                'coffee',
                'fruit',
                'daily_housekeeping',
                'ironing_service',
                'breakfast',
                'cultural_tours',
                'garden',
                'terrace',
                'outdoor_furniture',
                'picnic_area',
                '24_7_reception',
                'free_parking',
                'first_aid_kit',
                'lake_access',
                'assistance_animals_allowed',
                'self_check_in',
                'room_service',
                'airport_shuttle'
            ],
            // Set 3: Cultural/Educational Activity (14 features) - Cultural + basic amenities
            [
                'cultural_tours',
                'coffee',
                'fruit',
                'breakfast',
                'airport_shuttle',
                'free_parking',
                'garden',
                'first_aid_kit',
                'self_check_in',
                '24_7_reception',
                'bike_tours',
                'cycling_off_site',
                'hiking_off_site',
                'picnic_area'
            ],
            // Set 4: Water Sports Activity (16 features) - Equipment + safety
            [
                'bicycle_rental',
                'bbq_utensils',
                'breakfast',
                'coffee',
                'fruit',
                'first_aid_kit',
                'lake_access',
                'fishing_off_site',
                'free_parking',
                'outdoor_furniture',
                'picnic_area',
                'garden',
                'self_check_in',
                'airport_shuttle',
                'assistance_animals_allowed',
                'cycling_off_site'
            ],
            // Set 5: Eco/Nature Activity (12 features) - Nature focused
            [
                'bicycle_rental',
                'bbq_utensils',
                'coffee',
                'fruit',
                'garden',
                'lake_access',
                'hiking_off_site',
                'fishing_off_site',
                'bike_tours',
                'cycling_off_site',
                'picnic_area',
                'first_aid_kit'
            ],
        ];

        // Randomly pick one feature set
        $selectedFeatureValues = $featureSets[array_rand($featureSets)];

        // Attach features with order
        $order = 1;
        foreach ($selectedFeatureValues as $featureValue) {
            // Find feature in tenant database by value
            $feature = ResourceFeature::where('value', $featureValue)->first();

            if ($feature) {
                // Attach to activity via resource_features pivot table
                $activity->features()->syncWithoutDetaching([
                    $feature->feature_id => [
                        'order' => $order++,
                        'assigned_at' => now()->subDays(rand(1, 30)),
                    ]
                ]);
            }
        }
    }
}
