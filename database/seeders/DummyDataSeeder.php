<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
                'type' => 'bali',
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
            $tenant->type = $tenantData['type'];
            $tenant->industry = 'tourism';
            $tenant->location = $tenantData['type'] === 'bali' ? 'Bali, Indonesia' : ($tenantData['type'] === 'indonesian' ? 'Indonesia' : 'International');
            $tenant->resource_routes = $tenantData['resource_routes'] ?? [];
            $tenant->save();

            // Create domain
            $tenant->domains()->create([
                'domain' => $tenantData['domain'],
            ]);

            // Seed units and activities in tenant context
            tenancy()->initialize($tenant);

            foreach ($tenantData['units'] as $unitData) {
                Unit::create([
                    'name' => $unitData['name'],
                    'slug' => $unitData['slug'],
                ]);
            }

            // Seed activities from hardcoded tenant data
            foreach ($tenantData['activities'] as $activityData) {
                Activity::create([
                    'name' => $activityData['name'],
                    'slug' => $activityData['slug'],
                ]);
            }

            tenancy()->end();

            echo "  ✓ Created tenant: {$tenant->name} with " . count($tenantData['units']) . " units and " . count($tenantData['activities']) . " activities\n";
        }
    }
}
