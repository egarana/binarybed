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
        echo "🏢 Seeding 22 tenants...\n";
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
     * TENANT SEEDING - HARD CODED (22 tenants)
     * ==========================================
     */
    private function seedTenants(): void
    {
        $tenants = [
            // 50% Bali companies (11 tenants)
            [
                'id' => 'karmabeachresort',
                'name' => 'Karma Beach Resort',
                'domain' => 'karmabeachresort.test',
                'type' => 'bali',
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
                'id' => 'balihaidiving',
                'name' => 'Bali Hai Diving',
                'domain' => 'balihaidiving.test',
                'type' => 'bali',
                'units' => [
                    ['name' => 'Bamboo Cottage', 'slug' => 'bamboo-cottage'],
                    ['name' => 'Beach Bungalow', 'slug' => 'beach-bungalow'],
                    ['name' => 'Garden Cottage', 'slug' => 'garden-cottage'],
                    ['name' => 'Oceanfront Bungalow', 'slug' => 'oceanfront-bungalow'],
                ],
                'activities' => [
                    ['name' => 'Scuba Diving', 'slug' => 'scuba-diving'],
                    ['name' => 'Deep Sea Fishing', 'slug' => 'deep-sea-fishing'],
                    ['name' => 'Glass Bottom Boat Tour', 'slug' => 'glass-bottom-boat-tour'],
                    ['name' => 'Snorkeling Trip', 'slug' => 'snorkeling-trip'],
                    ['name' => 'Kayaking Tour', 'slug' => 'kayaking-tour'],
                ],
            ],
            [
                'id' => 'seminyakvillaretreat',
                'name' => 'Seminyak Villa Retreat',
                'domain' => 'seminyakvillaretreat.test',
                'type' => 'bali',
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
                'id' => 'canggusurfresort',
                'name' => 'Canggu Surf Resort',
                'domain' => 'canggusurfresort.test',
                'type' => 'bali',
                'units' => [
                    ['name' => 'Ocean View Suite', 'slug' => 'ocean-view-suite'],
                    ['name' => 'Standard Room', 'slug' => 'standard-room'],
                    ['name' => 'Family Suite', 'slug' => 'family-suite'],
                    ['name' => 'Surf Bungalow', 'slug' => 'surf-bungalow'],
                    ['name' => 'Surf Shop Meeting Room', 'slug' => 'surf-shop-meeting-room'],
                ],
                'activities' => [
                    ['name' => 'Surfing Lessons', 'slug' => 'surfing-lessons'],
                    ['name' => 'Stand-Up Paddleboarding', 'slug' => 'stand-up-paddleboarding'],
                    ['name' => 'Beach Bootcamp', 'slug' => 'beach-bootcamp'],
                    ['name' => 'BBQ Beach Dinner', 'slug' => 'bbq-beach-dinner'],
                    ['name' => 'Family Beach Games', 'slug' => 'family-beach-games'],
                ],
            ],
            [
                'id' => 'nusaduahospitality',
                'name' => 'Nusa Dua Hospitality',
                'domain' => 'nusaduahospitality.test',
                'type' => 'bali',
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
            [
                'id' => 'jimbaranbayhotel',
                'name' => 'Jimbaran Bay Hotel',
                'domain' => 'jimbaranbayhotel.test',
                'type' => 'bali',
                'units' => [
                    ['name' => 'Garden View Room', 'slug' => 'garden-view-room'],
                    ['name' => 'Ocean View Room', 'slug' => 'ocean-view-room'],
                    ['name' => 'Executive Suite', 'slug' => 'executive-suite'],
                    ['name' => 'Palm Bungalow', 'slug' => 'palm-bungalow'],
                    ['name' => 'Terrace Cottage', 'slug' => 'terrace-cottage'],
                ],
                'activities' => [
                    ['name' => 'Seafood BBQ Night', 'slug' => 'seafood-bbq-night'],
                    ['name' => 'Village Cultural Tour', 'slug' => 'village-cultural-tour'],
                    ['name' => 'Market Tour', 'slug' => 'market-tour'],
                    ['name' => 'Coffee Plantation Visit', 'slug' => 'coffee-plantation-visit'],
                ],
            ],
            [
                'id' => 'uluwatuoceanvilla',
                'name' => 'Uluwatu Ocean Villa',
                'domain' => 'uluwatuoceanvilla.test',
                'type' => 'bali',
                'units' => [
                    ['name' => 'Jungle Villa', 'slug' => 'jungle-villa'],
                    ['name' => 'Clifftop Villa', 'slug' => 'clifftop-villa'],
                    ['name' => 'Ocean View Villa', 'slug' => 'ocean-view-villa'],
                    ['name' => 'Sunset Villa', 'slug' => 'sunset-villa'],
                ],
                'activities' => [
                    ['name' => 'Sunset Dinner Cruise', 'slug' => 'sunset-dinner-cruise'],
                    ['name' => 'Clifftop Yoga', 'slug' => 'clifftop-yoga'],
                    ['name' => 'Jungle Trekking', 'slug' => 'jungle-trekking'],
                    ['name' => 'Stargazing Evening', 'slug' => 'stargazing-evening'],
                    ['name' => 'Private Villa BBQ', 'slug' => 'private-villa-bbq'],
                ],
            ],
            [
                'id' => 'kutabeachresort',
                'name' => 'Kuta Beach Resort',
                'domain' => 'kutabeachresort.test',
                'type' => 'bali',
                'units' => [
                    ['name' => 'Standard Room', 'slug' => 'standard-room'],
                    ['name' => 'Superior Room', 'slug' => 'superior-room'],
                    ['name' => 'Deluxe Room', 'slug' => 'deluxe-room'],
                    ['name' => 'Family Suite', 'slug' => 'family-suite'],
                    ['name' => 'Beach Front Villa', 'slug' => 'beach-front-villa'],
                    ['name' => 'Jasmine Function Room', 'slug' => 'jasmine-function-room'],
                ],
                'activities' => [
                    ['name' => 'Kids Club Activities', 'slug' => 'kids-club-activities'],
                    ['name' => 'Banana Boat Ride', 'slug' => 'banana-boat-ride'],
                    ['name' => 'Turtle Conservation Visit', 'slug' => 'turtle-conservation-visit'],
                    ['name' => 'Aqua Aerobics', 'slug' => 'aqua-aerobics'],
                    ['name' => 'Themed Dinner Night', 'slug' => 'themed-dinner-night'],
                ],
            ],
            [
                'id' => 'balitropicalgetaway',
                'name' => 'Bali Tropical Getaway',
                'domain' => 'balitropicalgetaway.test',
                'type' => 'bali',
                'units' => [
                    ['name' => 'Tropical Cottage', 'slug' => 'tropical-cottage'],
                    ['name' => 'Riverside Cottage', 'slug' => 'riverside-cottage'],
                    ['name' => 'Traditional Bungalow', 'slug' => 'traditional-bungalow'],
                    ['name' => 'Garden Pool Villa', 'slug' => 'garden-pool-villa'],
                    ['name' => 'Peaceful Wellness Room', 'slug' => 'peaceful-wellness-room'],
                ],
                'activities' => [
                    ['name' => 'Batik Workshop', 'slug' => 'batik-workshop'],
                    ['name' => 'Wood Carving Class', 'slug' => 'wood-carving-class'],
                    ['name' => 'Waterfall Tour', 'slug' => 'waterfall-tour'],
                    ['name' => 'Bird Watching Tour', 'slug' => 'bird-watching-tour'],
                    ['name' => 'Pilates Class', 'slug' => 'pilates-class'],
                ],
            ],

            // 30% Indonesian companies (7 tenants)
            [
                'id' => 'nusantaratravel',
                'name' => 'Nusantara Travel',
                'domain' => 'nusantaratravel.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Standard Room', 'slug' => 'standard-room'],
                    ['name' => 'Deluxe Suite', 'slug' => 'deluxe-suite'],
                    ['name' => 'Executive Meeting Room', 'slug' => 'executive-meeting-room'],
                    ['name' => 'Pavilion Conference Room', 'slug' => 'pavilion-conference-room'],
                ],
                'activities' => [
                    ['name' => 'Island Hopping Tour', 'slug' => 'island-hopping-tour'],
                    ['name' => 'Volcano Trekking', 'slug' => 'volcano-trekking'],
                    ['name' => 'Cultural Heritage Walk', 'slug' => 'cultural-heritage-walk'],
                    ['name' => 'Traditional Cuisine Tasting', 'slug' => 'traditional-cuisine-tasting'],
                ],
            ],
            [
                'id' => 'archipelagotours',
                'name' => 'Archipelago Tours',
                'domain' => 'archipelagotours.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Ocean View Room', 'slug' => 'ocean-view-room'],
                    ['name' => 'Garden View Room', 'slug' => 'garden-view-room'],
                    ['name' => 'Superior Suite', 'slug' => 'superior-suite'],
                    ['name' => 'Bamboo Cottage', 'slug' => 'bamboo-cottage'],
                    ['name' => 'Island Meeting Hall', 'slug' => 'island-meeting-hall'],
                ],
                'activities' => [
                    ['name' => 'Snorkeling Trip', 'slug' => 'snorkeling-trip'],
                    ['name' => 'Mangrove Kayaking', 'slug' => 'mangrove-kayaking'],
                    ['name' => 'Fishing Village Tour', 'slug' => 'fishing-village-tour'],
                    ['name' => 'Coral Reef Conservation', 'slug' => 'coral-reef-conservation'],
                    ['name' => 'Sunset Sailing', 'slug' => 'sunset-sailing'],
                ],
            ],
            [
                'id' => 'indonesiavacation',
                'name' => 'Indonesia Vacation',
                'domain' => 'indonesiavacation.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Deluxe Room', 'slug' => 'deluxe-room'],
                    ['name' => 'Family Room', 'slug' => 'family-room'],
                    ['name' => 'Executive Suite', 'slug' => 'executive-suite'],
                    ['name' => 'Villa Indonesia', 'slug' => 'villa-indonesia'],
                    ['name' => 'Boardroom Nusantara', 'slug' => 'boardroom-nusantara'],
                    ['name' => 'Spa Room Relaxation', 'slug' => 'spa-room-relaxation'],
                ],
                'activities' => [
                    ['name' => 'Archipelago Discovery', 'slug' => 'archipelago-discovery'],
                    ['name' => 'Traditional Massage', 'slug' => 'traditional-massage'],
                    ['name' => 'Local Market Experience', 'slug' => 'local-market-experience'],
                    ['name' => 'Batik Making Class', 'slug' => 'batik-making-class'],
                    ['name' => 'Gamelan Music Performance', 'slug' => 'gamelan-music-performance'],
                ],
            ],
            [
                'id' => 'garudahospitality',
                'name' => 'Garuda Hospitality',
                'domain' => 'garudahospitality.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Presidential Suite', 'slug' => 'presidential-suite'],
                    ['name' => 'Executive Suite', 'slug' => 'executive-suite'],
                    ['name' => 'Deluxe Suite', 'slug' => 'deluxe-suite'],
                    ['name' => 'Grand Ballroom Garuda', 'slug' => 'grand-ballroom-garuda'],
                    ['name' => 'Spa Suite Premium', 'slug' => 'spa-suite-premium'],
                ],
                'activities' => [
                    ['name' => 'Executive Wellness Program', 'slug' => 'executive-wellness-program'],
                    ['name' => 'Premium Tea Ceremony', 'slug' => 'premium-tea-ceremony'],
                    ['name' => 'Heritage City Tour', 'slug' => 'heritage-city-tour'],
                    ['name' => 'Luxury Spa Treatment', 'slug' => 'luxury-spa-treatment'],
                ],
            ],
            [
                'id' => 'merahputihresort',
                'name' => 'Merah Putih Resort',
                'domain' => 'merahputihresort.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Garden Villa', 'slug' => 'garden-villa'],
                    ['name' => 'Pool Villa', 'slug' => 'pool-villa'],
                    ['name' => 'Beach Cottage', 'slug' => 'beach-cottage'],
                    ['name' => 'Mountain View Room', 'slug' => 'mountain-view-room'],
                    ['name' => 'Conference Hall Indonesia', 'slug' => 'conference-hall-indonesia'],
                    ['name' => 'Wellness Center', 'slug' => 'wellness-center'],
                ],
                'activities' => [
                    ['name' => 'Mountain Biking', 'slug' => 'mountain-biking'],
                    ['name' => 'Tea Plantation Tour', 'slug' => 'tea-plantation-tour'],
                    ['name' => 'Organic Farming Experience', 'slug' => 'organic-farming-experience'],
                    ['name' => 'Nature Photography Walk', 'slug' => 'nature-photography-walk'],
                    ['name' => 'Wellness Retreat', 'slug' => 'wellness-retreat'],
                ],
            ],
            [
                'id' => 'indahtours',
                'name' => 'PT Indah Tours',
                'domain' => 'indahtours.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Superior Room', 'slug' => 'superior-room'],
                    ['name' => 'Standard Room', 'slug' => 'standard-room'],
                    ['name' => 'Bungalow Indah', 'slug' => 'bungalow-indah'],
                    ['name' => 'Meeting Room Indah', 'slug' => 'meeting-room-indah'],
                ],
                'activities' => [
                    ['name' => 'River Tubing', 'slug' => 'river-tubing'],
                    ['name' => 'Traditional Performance', 'slug' => 'traditional-performance'],
                    ['name' => 'Handicraft Workshop', 'slug' => 'handicraft-workshop'],
                ],
            ],
            [
                'id' => 'garudatravelindonesia',
                'name' => 'Garuda Travel Indonesia',
                'domain' => 'garudatravelindonesia.test',
                'type' => 'indonesian',
                'units' => [
                    ['name' => 'Executive Room', 'slug' => 'executive-room'],
                    ['name' => 'Deluxe Room', 'slug' => 'deluxe-room'],
                    ['name' => 'Suite Garuda', 'slug' => 'suite-garuda'],
                    ['name' => 'Villa Nusantara', 'slug' => 'villa-nusantara'],
                    ['name' => 'Function Hall Garuda', 'slug' => 'function-hall-garuda'],
                    ['name' => 'Spa Therapy Room', 'slug' => 'spa-therapy-room'],
                ],
                'activities' => [
                    ['name' => 'Indonesian Cooking Class', 'slug' => 'indonesian-cooking-class'],
                    ['name' => 'Spice Garden Tour', 'slug' => 'spice-garden-tour'],
                    ['name' => 'Traditional Music Workshop', 'slug' => 'traditional-music-workshop'],
                    ['name' => 'Herbal Spa Treatment', 'slug' => 'herbal-spa-treatment'],
                    ['name' => 'Cultural Dance Class', 'slug' => 'cultural-dance-class'],
                ],
            ],

            // 20% International companies (4 tenants)
            [
                'id' => 'sunsetvillamanagement',
                'name' => 'Sunset Villa Management',
                'domain' => 'sunsetvillamanagement.test',
                'type' => 'international',
                'units' => [
                    ['name' => 'Luxury Villa', 'slug' => 'luxury-villa'],
                    ['name' => 'Premium Villa', 'slug' => 'premium-villa'],
                    ['name' => 'Sunset Villa', 'slug' => 'sunset-villa'],
                    ['name' => 'Ocean Villa', 'slug' => 'ocean-villa'],
                    ['name' => 'Executive Boardroom', 'slug' => 'executive-boardroom'],
                    ['name' => 'Luxury Spa Suite', 'slug' => 'luxury-spa-suite'],
                ],
                'activities' => [
                    ['name' => 'Private Yacht Charter', 'slug' => 'private-yacht-charter'],
                    ['name' => 'Helicopter Island Tour', 'slug' => 'helicopter-island-tour'],
                    ['name' => 'Premium Wine Pairing', 'slug' => 'premium-wine-pairing'],
                    ['name' => 'Personal Butler Service', 'slug' => 'personal-butler-service'],
                    ['name' => 'Luxury Sunset Cruise', 'slug' => 'luxury-sunset-cruise'],
                ],
            ],
            [
                'id' => 'tropicalgetawaysintl',
                'name' => 'Tropical Getaways International',
                'domain' => 'tropicalgetawaysintl.test',
                'type' => 'international',
                'units' => [
                    ['name' => 'Paradise Suite', 'slug' => 'paradise-suite'],
                    ['name' => 'Tropical Villa', 'slug' => 'tropical-villa'],
                    ['name' => 'Beachfront Villa', 'slug' => 'beachfront-villa'],
                    ['name' => 'Premium Room', 'slug' => 'premium-room'],
                    ['name' => 'International Meeting Hall', 'slug' => 'international-meeting-hall'],
                ],
                'activities' => [
                    ['name' => 'Champagne Beach Breakfast', 'slug' => 'champagne-beach-breakfast'],
                    ['name' => 'Private Scuba Diving', 'slug' => 'private-scuba-diving'],
                    ['name' => 'Gourmet Dining Experience', 'slug' => 'gourmet-dining-experience'],
                    ['name' => 'Tropical Cocktail Class', 'slug' => 'tropical-cocktail-class'],
                ],
            ],
            [
                'id' => 'paradiseresortgroup',
                'name' => 'Paradise Resort Group',
                'domain' => 'paradiseresortgroup.test',
                'type' => 'international',
                'units' => [
                    ['name' => 'Royal Suite', 'slug' => 'royal-suite'],
                    ['name' => 'Imperial Suite', 'slug' => 'imperial-suite'],
                    ['name' => 'Garden Villa Premium', 'slug' => 'garden-villa-premium'],
                    ['name' => 'Ocean Villa Deluxe', 'slug' => 'ocean-villa-deluxe'],
                    ['name' => 'Paradise Ballroom', 'slug' => 'paradise-ballroom'],
                    ['name' => 'Royal Spa Center', 'slug' => 'royal-spa-center'],
                    ['name' => 'Premium Conference Room', 'slug' => 'premium-conference-room'],
                ],
                'activities' => [
                    ['name' => 'Five-Star Fine Dining', 'slug' => 'five-star-fine-dining'],
                    ['name' => 'Executive Golf Package', 'slug' => 'executive-golf-package'],
                    ['name' => 'Royal Spa Experience', 'slug' => 'royal-spa-experience'],
                    ['name' => 'Luxury Shopping Tour', 'slug' => 'luxury-shopping-tour'],
                    ['name' => 'Premium Entertainment Night', 'slug' => 'premium-entertainment-night'],
                    ['name' => 'Private Island Experience', 'slug' => 'private-island-experience'],
                ],
            ],
            [
                'id' => 'luxuryescapesworldwide',
                'name' => 'Luxury Escapes Worldwide',
                'domain' => 'luxuryescapesworldwide.test',
                'type' => 'international',
                'units' => [
                    ['name' => 'Penthouse Suite', 'slug' => 'penthouse-suite'],
                    ['name' => 'Presidential Villa', 'slug' => 'presidential-villa'],
                    ['name' => 'Executive Villa', 'slug' => 'executive-villa'],
                    ['name' => 'Luxury Suite', 'slug' => 'luxury-suite'],
                    ['name' => 'Diamond Ballroom', 'slug' => 'diamond-ballroom'],
                    ['name' => 'Elite Spa Pavilion', 'slug' => 'elite-spa-pavilion'],
                ],
                'activities' => [
                    ['name' => 'VIP Airport Transfer', 'slug' => 'vip-airport-transfer'],
                    ['name' => 'Michelin Star Dining', 'slug' => 'michelin-star-dining'],
                    ['name' => 'Exclusive Beach Club', 'slug' => 'exclusive-beach-club'],
                    ['name' => 'Private Jet Tour', 'slug' => 'private-jet-tour'],
                    ['name' => 'Celebrity Chef Experience', 'slug' => 'celebrity-chef-experience'],
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create tenant
            $tenant = Tenant::create([
                'id' => $tenantData['id'],
                'name' => $tenantData['name'],
                'data' => json_encode([
                    'type' => $tenantData['type'],
                    'industry' => 'tourism',
                    'location' => $tenantData['type'] === 'bali' ? 'Bali, Indonesia' : ($tenantData['type'] === 'indonesian' ? 'Indonesia' : 'International'),
                ]),
            ]);

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
