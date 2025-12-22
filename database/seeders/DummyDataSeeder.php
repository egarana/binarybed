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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    private $faker;
    private $createdUsers = [];

    public function __construct()
    {
        $this->faker = Faker::create('id_ID'); // Indonesian locale
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🌱 Starting dummy data seeding...\n";

        // Seed Users
        echo "👥 Seeding users...\n";
        $this->seedUsers(30);

        // Seed Tenants
        echo "🏢 Seeding tenants...\n";
        $this->seedTenants(10);

        // Seed Reservations
        echo "📅 Seeding reservations...\n";
        $this->seedReservations();

        echo "✅ Dummy data seeding completed!\n";
    }

    /**
     * Seed users with Faker
     */
    private function seedUsers(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            // Mix of Indonesian and international names
            $isIndonesian = $this->faker->boolean(70); // 70% Indonesian names

            if ($isIndonesian) {
                $name = $this->faker->name();
            } else {
                $fakerEn = Faker::create('en_US');
                $name = $fakerEn->name();
            }

            $email = $this->generateEmailFromName($name);

            $user = User::create([
                'global_id' => Str::uuid()->toString(),
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);

            $this->createdUsers[] = $user;
        }

        echo "  ✓ Created {$count} users\n";
    }

    /**
     * Seed tenants with dynamic data
     */
    private function seedTenants(int $count): void
    {
        $businessTypes = [
            'resort' => ['units' => true, 'activities' => true],
            'hotel' => ['units' => true, 'activities' => true],
            'villa' => ['units' => true, 'activities' => true],
            'spa' => ['units' => true, 'activities' => true],
            'diving_center' => ['units' => false, 'activities' => true],
            'surf_school' => ['units' => false, 'activities' => true],
            'adventure_tours' => ['units' => false, 'activities' => true],
            'cultural_center' => ['units' => false, 'activities' => true],
            'wellness_hub' => ['units' => false, 'activities' => true],
        ];

        $businessTypeKeys = array_keys($businessTypes);

        for ($i = 0; $i < $count; $i++) {
            $businessType = $businessTypeKeys[array_rand($businessTypeKeys)];
            $config = $businessTypes[$businessType];

            // Generate business name and ensure unique tenant ID
            $businessName = $this->generateBusinessName($businessType);
            $tenantId = Str::slug($businessName, '');

            // Ensure unique tenant ID
            $counter = 1;
            $originalTenantId = $tenantId;
            while (Tenant::where('id', $tenantId)->exists()) {
                $tenantId = $originalTenantId . $counter;
                $counter++;
            }

            $domain = $tenantId . '.test';

            // Create tenant
            $tenant = Tenant::create([
                'id' => $tenantId,
                'name' => $businessName,
            ]);

            // Set resource routes
            $resourceRoutes = [];
            if ($config['units']) {
                $resourceRoutes[$this->getAccommodationKey($businessType)] = 'units';
            }
            if ($config['activities']) {
                $resourceRoutes[$this->getExperienceKey($businessType)] = 'activities';
            }

            $tenant->resource_routes = $resourceRoutes;
            $tenant->save();

            // Create domain
            $tenant->domains()->create(['domain' => $domain]);

            // Seed in tenant context
            tenancy()->initialize($tenant);

            // Sync features
            $this->syncFeaturesToTenant();

            // Seed units if applicable
            $unitCount = 0;
            if ($config['units']) {
                $unitCount = $this->faker->numberBetween(3, 7);
                for ($j = 0; $j < $unitCount; $j++) {
                    $this->createUnit($businessType);
                }
            }

            // Seed activities if applicable
            $activityCount = 0;
            if ($config['activities']) {
                $activityCount = $this->faker->numberBetween(4, 6);
                for ($j = 0; $j < $activityCount; $j++) {
                    $this->createActivity($businessType);
                }
            }

            tenancy()->end();

            echo "  ✓ Created tenant: {$businessName} ({$unitCount} units, {$activityCount} activities)\n";
        }
    }

    /**
     * Generate business name based on type
     */
    private function generateBusinessName(string $type): string
    {
        $prefixes = ['Bali', 'Sanur', 'Ubud', 'Canggu', 'Seminyak', 'Nusa Dua', 'Karma', 'Paradise'];
        $prefix = $this->faker->randomElement($prefixes);

        $suffixes = [
            'resort' => ['Resort', 'Beach Resort', 'Luxury Resort'],
            'hotel' => ['Hotel', 'Paradise Hotel', 'Grand Hotel'],
            'villa' => ['Villa Retreat', 'Villa', 'Villas'],
            'spa' => ['Spa', 'Garden Spa', 'Wellness Spa'],
            'diving_center' => ['Diving Center', 'Dive Club', 'Scuba Center'],
            'surf_school' => ['Surf School', 'Surf Academy', 'Wave School'],
            'adventure_tours' => ['Adventure Tours', 'Adventures', 'Expeditions'],
            'cultural_center' => ['Cultural Experiences', 'Cultural Center', 'Heritage Tours'],
            'wellness_hub' => ['Wellness Hub', 'Wellness Center', 'Holistic Retreat'],
        ];

        $suffix = $this->faker->randomElement($suffixes[$type] ?? ['Resort']);

        return $prefix . ' ' . $suffix;
    }

    /**
     * Get accommodation route key based on business type
     */
    private function getAccommodationKey(string $type): string
    {
        $keys = [
            'resort' => ['accommodations', 'rooms'],
            'hotel' => ['rooms', 'accommodations'],
            'villa' => ['villas', 'accommodations'],
            'spa' => ['spa-rooms', 'treatment-rooms'],
        ];

        return $this->faker->randomElement($keys[$type] ?? ['units']);
    }

    /**
     * Get experience route key based on business type
     */
    private function getExperienceKey(string $type): string
    {
        $keys = [
            'resort' => ['experiences', 'activities'],
            'hotel' => ['experiences', 'services'],
            'villa' => ['experiences', 'activities'],
            'spa' => ['treatments', 'services'],
            'diving_center' => ['dive-courses', 'programs'],
            'surf_school' => ['lessons', 'courses'],
            'adventure_tours' => ['tours', 'adventures'],
            'cultural_center' => ['classes', 'workshops'],
            'wellness_hub' => ['programs', 'sessions'],
        ];

        return $this->faker->randomElement($keys[$type] ?? ['activities']);
    }

    /**
     * Create a unit with Faker data
     */
    private function createUnit(string $businessType): void
    {
        $unitTypes = [
            'resort' => ['Ocean View Villa', 'Garden Pool Villa', 'Beach Front Villa', 'Deluxe Suite', 'Presidential Suite'],
            'hotel' => ['Superior Room', 'Deluxe Room', 'Executive Suite', 'Junior Suite', 'Presidential Suite'],
            'villa' => ['Tropical Villa', 'Sunset Villa', 'Private Garden Villa', 'Clifftop Villa', 'Pool Villa'],
            'spa' => ['Therapy Room', 'Massage Suite', 'Spa Chamber', 'Treatment Room', 'Wellness Pavilion'],
        ];

        $types = $unitTypes[$businessType] ?? ['Standard Unit', 'Premium Unit', 'Deluxe Unit'];
        $typeName = $this->faker->randomElement($types);

        // Add variation with adjectives
        $adjectives = ['', 'Luxury ', 'Premium ', 'Exclusive ', 'Superior '];
        $adjective = $this->faker->randomElement($adjectives);

        $name = $adjective . $typeName;
        $slug = Str::slug($name);

        // Ensure unique slug
        $counter = 1;
        $originalSlug = $slug;
        while (Unit::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $unit = Unit::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        // Create rates
        $this->createRatesForUnit($unit);

        // Attach users
        $this->attachUsersToResource($unit, 'unit');

        // Attach features
        $this->attachFeaturesToResource($unit, 'unit');
    }

    /**
     * Create an activity with Faker data
     */
    private function createActivity(string $businessType): void
    {
        $activityTypes = [
            'resort' => ['Massage', 'Yoga Class', 'Surfing Lessons', 'Snorkeling Trip', 'Sunset Cruise'],
            'hotel' => ['Traditional Dance Show', 'Cooking Class', 'Temple Tour', 'Spa Treatment'],
            'villa' => ['Private Beach Dining', 'Couples Spa Package', 'Romantic Photoshoot', 'Cocktail Ceremony'],
            'spa' => ['Aromatherapy Session', 'Meditation Session', 'Body Wrap', 'Facial Treatment'],
            'diving_center' => ['Scuba Diving', 'Deep Sea Fishing', 'Snorkeling Trip', 'Boat Tour', 'Underwater Photography'],
            'surf_school' => ['Surfing Lessons', 'Stand-Up Paddleboarding', 'Beach Bootcamp', 'Surf Photography'],
            'adventure_tours' => ['ATV Riding', 'White Water Rafting', 'Jungle Trekking', 'Waterfall Tour', 'Cycling Tour'],
            'cultural_center' => ['Cooking Class', 'Dance Workshop', 'Batik Making', 'Wood Carving', 'Temple Tour'],
            'wellness_hub' => ['Yoga Retreat', 'Sound Healing', 'Reiki Healing', 'Breathwork Workshop', 'Detox Program'],
        ];

        $types = $activityTypes[$businessType] ?? ['Experience', 'Activity', 'Session'];
        $typeName = $this->faker->randomElement($types);

        // Add variation
        $prefixes = ['', 'Balinese ', 'Traditional ', 'Modern ', 'Authentic '];
        $prefix = $this->faker->randomElement($prefixes);

        $name = $prefix . $typeName;
        $slug = Str::slug($name);

        // Ensure unique slug
        $counter = 1;
        $originalSlug = $slug;
        while (Activity::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $activity = Activity::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        // Create rates
        $this->createRatesForActivity($activity);

        // Attach users
        $this->attachUsersToResource($activity, 'activity');

        // Attach features
        $this->attachFeaturesToResource($activity, 'activity');
    }

    /**
     * Create rates for a unit
     */
    private function createRatesForUnit(Unit $unit): void
    {
        $rateCount = $this->faker->numberBetween(2, 4);

        $rateTypes = [
            'Standard Rate',
            'Weekend Rate',
            'Peak Season Rate',
            'Early Bird Rate',
            'Last Minute Rate',
            'Long Stay Rate',
            'Corporate Rate',
        ];

        $usedTypes = [];

        for ($i = 0; $i < $rateCount; $i++) {
            // Pick unique rate type
            $availableTypes = array_diff($rateTypes, $usedTypes);
            if (empty($availableTypes)) break;

            $rateName = $this->faker->randomElement($availableTypes);
            $usedTypes[] = $rateName;

            $unit->rates()->create([
                'name' => $rateName,
                'slug' => Str::slug($rateName),
                'description' => $this->generateRateDescription($rateName),
                'price' => $this->faker->numberBetween(500, 5000) * 1000, // 500k - 5M IDR
                'currency' => 'IDR',
                'price_type' => 'night', // Units are typically priced per night
                'is_active' => true,
            ]);
        }
    }

    /**
     * Create rates for an activity
     */
    private function createRatesForActivity(Activity $activity): void
    {
        $rateCount = $this->faker->numberBetween(2, 4);

        $rateTypes = [
            'Standard Rate',
            'Group Rate',
            'Private Session',
            'Couple Package',
            'Family Package',
            'Peak Hours Rate',
            'Off-Peak Rate',
        ];

        $usedTypes = [];

        for ($i = 0; $i < $rateCount; $i++) {
            // Pick unique rate type
            $availableTypes = array_diff($rateTypes, $usedTypes);
            if (empty($availableTypes)) break;

            $rateName = $this->faker->randomElement($availableTypes);
            $usedTypes[] = $rateName;

            // Determine price type based on rate name
            $priceType = match (true) {
                str_contains($rateName, 'Group') => 'group',
                str_contains($rateName, 'Private') => 'session',
                str_contains($rateName, 'Couple') => 'couple',
                str_contains($rateName, 'Family') => 'group',
                default => 'person',
            };

            $activity->rates()->create([
                'name' => $rateName,
                'slug' => Str::slug($rateName),
                'description' => $this->generateRateDescription($rateName),
                'price' => $this->faker->numberBetween(150, 2000) * 1000, // 150k - 2M IDR
                'currency' => 'IDR',
                'price_type' => $priceType,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Generate rate description
     */
    private function generateRateDescription(string $rateName): string
    {
        $descriptions = [
            'Standard Rate' => 'Our regular pricing for all year round bookings',
            'Weekend Rate' => 'Special pricing for Friday, Saturday and Sunday',
            'Peak Season Rate' => 'High season pricing during peak months',
            'Early Bird Rate' => 'Book in advance and save',
            'Last Minute Rate' => 'Limited availability for last minute bookings',
            'Long Stay Rate' => 'Discounted rate for extended stays',
            'Corporate Rate' => 'Special pricing for business travelers',
            'Group Rate' => 'Discounted rate for groups',
            'Private Session' => 'Exclusive one-on-one experience',
            'Couple Package' => 'Perfect package for two people',
            'Family Package' => 'Great value for families',
            'Peak Hours Rate' => 'Premium time slots with high demand',
            'Off-Peak Rate' => 'Best value during quieter times',
        ];

        return $descriptions[$rateName] ?? $this->faker->sentence();
    }

    /**
     * Attach users to resource (unit or activity)
     */
    private function attachUsersToResource($resource, string $type): void
    {
        // 60% chance to attach users
        if (!$this->faker->boolean(60)) {
            return;
        }

        $userCount = $this->faker->numberBetween(1, 3);
        $selectedUsers = $this->faker->randomElements($this->createdUsers, $userCount);

        foreach ($selectedUsers as $centralUser) {
            // Sync user to tenant database
            $tenantUser = UserTenant::firstOrCreate(
                ['global_id' => $centralUser->global_id],
                [
                    'name' => $centralUser->name,
                    'email' => $centralUser->email,
                ]
            );

            // Attach with role
            $role = $this->faker->randomElement(['partner', 'referrer']);
            $daysAgo = $this->faker->numberBetween(1, 90);

            $resource->users()->syncWithoutDetaching([
                $tenantUser->global_id => [
                    'role' => $role,
                    'assigned_at' => now()->subDays($daysAgo),
                ]
            ]);
        }
    }

    /**
     * Attach features to resource
     */
    private function attachFeaturesToResource($resource, string $type): void
    {
        $featureCount = $this->faker->numberBetween(12, 25);

        // Get all available features
        $allFeatures = ResourceFeature::all();

        if ($allFeatures->isEmpty()) {
            return;
        }

        // Randomly select features
        $selectedFeatures = $allFeatures->random(min($featureCount, $allFeatures->count()));

        $order = 1;
        foreach ($selectedFeatures as $feature) {
            $resource->features()->syncWithoutDetaching([
                $feature->feature_id => [
                    'order' => $order++,
                    'assigned_at' => now()->subDays($this->faker->numberBetween(1, 30)),
                ]
            ]);
        }
    }

    /**
     * Seed reservations for all tenants
     */
    private function seedReservations(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $reservationCount = $this->faker->numberBetween(5, 15);

            for ($i = 0; $i < $reservationCount; $i++) {
                $this->createReservation();
            }

            echo "  ✓ Created {$reservationCount} reservations for: {$tenant->name}\n";

            tenancy()->end();
        }
    }

    /**
     * Create a single reservation with items
     */
    private function createReservation(): void
    {
        $guestName = $this->faker->name();
        $guestEmail = $this->generateEmailFromName($guestName);

        // Random status distribution
        $statuses = [
            Reservation::STATUS_PENDING => 15,
            Reservation::STATUS_CONFIRMED => 50,
            Reservation::STATUS_COMPLETED => 20,
            Reservation::STATUS_CANCELLED => 10,
            Reservation::STATUS_NO_SHOW => 5,
        ];

        $status = $this->faker->randomElement(
            array_merge(...array_map(
                fn($status, $weight) => array_fill(0, $weight, $status),
                array_keys($statuses),
                array_values($statuses)
            ))
        );

        $reservation = Reservation::create([
            'code' => Reservation::generateUniqueCode(),
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => json_encode([
                'country_code' => '+62',
                'number' => '8' . $this->faker->numerify('#########'),
            ]),
            'status' => $status,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'cancellation_reason' => $status === Reservation::STATUS_CANCELLED
                ? $this->faker->randomElement(['Guest changed plans', 'Payment issue', 'Schedule conflict', 'Found alternative'])
                : null,
        ]);

        // Add reservation items (1-4 items)
        $itemCount = $this->faker->numberBetween(1, 4);

        for ($j = 0; $j < $itemCount; $j++) {
            // 50% unit, 50% activity
            if ($this->faker->boolean() && Unit::count() > 0) {
                $this->createUnitReservationItem($reservation);
            } elseif (Activity::count() > 0) {
                $this->createActivityReservationItem($reservation);
            }
        }

        // Recalculate totals
        $reservation->recalculateTotals();
        $reservation->save();
    }

    /**
     * Create unit reservation item
     */
    private function createUnitReservationItem(Reservation $reservation): void
    {
        $unit = Unit::inRandomOrder()->first();
        if (!$unit) return;

        $rate = $unit->rates()->where('is_active', true)->inRandomOrder()->first();
        if (!$rate) return;

        $startDate = now()->addDays($this->faker->numberBetween(1, 90));
        $durationDays = $this->faker->numberBetween(1, 14);
        $endDate = $startDate->copy()->addDays($durationDays);
        $quantity = $this->faker->numberBetween(1, 3);

        $ratePrice = $rate->price;
        $lineTotal = $quantity * $durationDays * $ratePrice;

        ReservationItem::create([
            'reservation_id' => $reservation->id,
            'reservable_type' => Unit::class,
            'reservable_id' => $unit->id,
            'rate_id' => $rate->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'quantity' => $quantity,
            'resource_name' => $unit->name,
            'resource_type_label' => 'Unit',
            'rate_name' => $rate->name,
            'rate_description' => $rate->description,
            'pricing_type' => ReservationItem::PRICING_PER_NIGHT,
            'rate_price' => $ratePrice,
            'currency' => 'IDR',
            'line_total' => $lineTotal,
            'status' => ReservationItem::STATUS_ACTIVE,
        ]);
    }

    /**
     * Create activity reservation item
     */
    private function createActivityReservationItem(Reservation $reservation): void
    {
        $activity = Activity::inRandomOrder()->first();
        if (!$activity) return;

        $rate = $activity->rates()->where('is_active', true)->inRandomOrder()->first();
        if (!$rate) return;

        $startDate = now()->addDays($this->faker->numberBetween(1, 90));
        $startTime = sprintf('%02d:00:00', $this->faker->numberBetween(8, 17));
        $durationMinutes = $this->faker->randomElement([60, 90, 120, 180, 240]);
        $endTime = date('H:i:s', strtotime($startTime) + ($durationMinutes * 60));
        $quantity = $this->faker->numberBetween(1, 6);

        $ratePrice = $rate->price;
        $lineTotal = $quantity * $ratePrice;

        ReservationItem::create([
            'reservation_id' => $reservation->id,
            'reservable_type' => Activity::class,
            'reservable_id' => $activity->id,
            'rate_id' => $rate->id,
            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'quantity' => $quantity,
            'resource_name' => $activity->name,
            'resource_type_label' => 'Activity',
            'rate_name' => $rate->name,
            'rate_description' => $rate->description,
            'pricing_type' => ReservationItem::PRICING_PER_PERSON,
            'rate_price' => $ratePrice,
            'currency' => 'IDR',
            'line_total' => $lineTotal,
            'status' => ReservationItem::STATUS_ACTIVE,
        ]);
    }

    /**
     * Sync features from central to tenant database
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
    }

    /**
     * Generate email from name
     */
    private function generateEmailFromName(string $name): string
    {
        $email = strtolower(str_replace(' ', '.', $name));
        $email = $this->removeAccents($email);
        $email = preg_replace('/[^a-z0-9.]/', '', $email);

        return $email . '@example.com';
    }

    /**
     * Remove accents from string
     */
    private function removeAccents(string $string): string
    {
        $transliteration = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'ñ' => 'n',
            'Ñ' => 'n',
            'ç' => 'c',
            'Ç' => 'c',
        ];

        return strtr($string, $transliteration);
    }
}
