<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Rate;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to avoid FK constraint errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data in correct order
        Rate::truncate();
        Unit::truncate();
        Property::truncate();
        User::where('id', '!=', 1)->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // === 1. Create Users ===
        $userNames = [
            'Made Santosa', 'John Peterson', 'Ayu Lestari', 'Clara Mitchell', 'Gede Wirawan', 'Sophia Chan',
            'Budi Hartono', 'Emma Wilson', 'Ketut Adi', 'Liam Brown', 'Nyoman Sari', 'Olivia Davis',
            'Wayan Putra', 'Maya Chen', 'I Ketut Arta', 'Alexander White', 'Rizky Pratama', 'Charlotte Lee',
            'Dewa Ananta', 'Isabella Moore', 'Kadek Surya', 'William Scott', 'Agung Dharma', 'Emily Carter',
            'Putu Lestari', 'James Turner', 'Ni Luh Ayu', 'Benjamin King', 'Cokorda Rai',
            
            // CHANGE: added more names to triple data
            'Andi Saputra', 'Sarah Johnson', 'Michael Tan', 'Jessica Lim', 'Teguh Santoso', 'Linda Park',
            'Arif Gunawan', 'Natalie Young', 'Sutanto Widjaja', 'Grace Wong', 'Daniel Harris', 'Elena Martinez',
            'Hendra Wijaya', 'Fiona Bell', 'Christopher Kim'
        ];

        $users = [];
        foreach ($userNames as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@gmail.com';
            $users[] = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
            ]);
        }

        // === 2. Create Properties ===
        $propertyNames = [
            'Villa Nirwana Ubud', 'Sanur Beach Resort', 'Canggu Sunset Guesthouse', 'Lotus Boutique Villa Seminyak',
            'Amed Ocean View Retreat', 'Nusa Penida Paradise Resort', 'Uluwatu Cliffside Villa', 'Kuta Lagoon Hotel',
            'Jimbaran Bay Villas', 'Sidemen Rice Terrace Lodge', 'Lovina Dolphin Resort', 'Munduk Mountain Lodge',
            'Padangbai Beachfront Retreat', 'Bingin Surf Bungalows', 'Pemuteran Coral Resort', 'Balian River Lodge',
            'Tabanan Heritage Villa', 'Tegallalang Jungle Retreat', 'Candidasa Ocean Breeze Resort', 'Legian Garden Hotel',
            'Kerobokan Serenity Villas', 'Gianyar Cultural Stay', 'Tanah Lot Seaview Lodge', 'Pererenan Hideaway Villas',

            // CHANGE: extra properties to triple dataset
            'Bukit Sunrise Villa', 'Menjangan Forest Lodge', 'Serangan Bay Resort', 'Denpasar City Hotel',
            'Singaraja Heritage Stay', 'Tulamben Dive Resort', 'Jatiluwih Green Escape', 'Batubulan Cultural Villas',
            'Bangli Mountain View Lodge', 'Negara Coastal Retreat', 'Kintamani Lakefront Villa', 'Karangasem Royal Villas'
        ];

        $properties = [];
        foreach ($propertyNames as $i => $pName) {
            $properties[] = Property::create([
                'name' => $pName,
                'domain' => strtolower(str_replace(' ', '', preg_replace('/[^A-Za-z0-9 ]/', '', $pName))) . '.com',
            ]);
        }

        // === 3. Assign Users to Properties ===
        foreach ($properties as $property) {
            $assignedUsers = collect($users)->random(rand(3, 6))->pluck('id');
            $property->users()->attach($assignedUsers);
        }

        // === 4. Unit Names ===
        $unitNames = [
            'Sunset Suite', 'Lotus Room', 'Ocean Breeze Villa', 'Garden View Room', 'Rice Terrace Suite',
            'Honeymoon Pavilion', 'Coral Bay Bungalow', 'Frangipani Cottage', 'Bamboo Hideout', 'Infinity Pool Villa',
            'Balinese Joglo', 'Sea Breeze Suite', 'Coconut Grove Room', 'Skyline Penthouse', 'Beachfront Pavilion',

            // CHANGE: extra unit names for bigger dataset
            'Jungle View Room', 'Cliff Edge Villa', 'Lagoon Access Suite', 'Palm Garden Villa', 'Volcano View Lodge',
            'Spa Retreat Room', 'Sunrise Bungalow', 'Riverside Pavilion', 'Royal Balinese Villa', 'Eco Bamboo House'
        ];

        // === 5. Rate Options ===
        $rateOptions = [
            ['name' => 'Standard Rate', 'min' => 400000, 'max' => 700000],
            ['name' => 'Weekend Special', 'min' => 450000, 'max' => 750000],
            ['name' => 'Honeymoon Special', 'min' => 800000, 'max' => 1200000],
            ['name' => 'Early Bird Discount', 'min' => 350000, 'max' => 650000],
            ['name' => 'Luxury Escape', 'min' => 1500000, 'max' => 2500000],
            ['name' => 'Last Minute Deal', 'min' => 380000, 'max' => 680000],
            ['name' => 'Family Package', 'min' => 900000, 'max' => 1500000],
        ];

        // CHANGE: helper for clean rounded prices
        function nicePrice($min, $max, $step = 50000) {
            $range = range($min, $max, $step);
            return $range[array_rand($range)];
        }

        // === 6. Create Units and Rates ===
        foreach ($properties as $property) {
            $unitCount = rand(3, 5);
            $chosenUnitNames = collect($unitNames)->random($unitCount);

            foreach ($chosenUnitNames as $unitName) {
                $unit = Unit::create([
                    'property_id' => $property->id,
                    'name' => $unitName,
                    'qty' => rand(2, 10),
                    'features' => collect(range(1, 36))
                        ->random(rand(10, 25)) // pick 10–25 random features
                        ->values()
                        ->toArray(),
                ]);

                // Always add Standard Rate first
                $standardRate = collect($rateOptions)->firstWhere('name', 'Standard Rate');
                Rate::create([
                    'unit_id' => $unit->id,
                    'name' => $standardRate['name'],
                    'price' => nicePrice($standardRate['min'], $standardRate['max']),
                ]);

                // Add 1–3 additional random rates (excluding Standard Rate)
                $extraRates = collect($rateOptions)
                    ->where('name', '!=', 'Standard Rate')
                    ->random(rand(1, 3));

                foreach ($extraRates as $rate) {
                    Rate::create([
                        'unit_id' => $unit->id,
                        'name' => $rate['name'],
                        'price' => nicePrice($rate['min'], $rate['max']),
                    ]);
                }
            }
        }

        $faker = Faker::create();

        // === 7. Create Reservations for each Unit ===
        foreach ($properties as $property) {
            foreach ($property->units as $unit) {
                // Each unit gets 12–15 reservations
                $reservationCount = rand(12, 15);

                for ($i = 0; $i < $reservationCount; $i++) {
                    // Random check-in date between today and 90 days from now
                    $checkIn = $faker->dateTimeBetween('now', '+90 days');

                    // Random stay length 1–7 days
                    $checkOut = (clone $checkIn)->modify('+' . rand(1, 7) . ' days');

                    // Booked_on should be before check-in
                    $bookedOn = $faker->dateTimeBetween('-30 days', $checkIn);

                    // Pick a random rate for this unit
                    $rate = $unit->rates->random();

                    Reservation::create([
                        'unit_id' => $unit->id,
                        'rate_id' => $rate->id ?? null,
                        'first_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'email' => $faker->unique()->safeEmail,
                        'phone' => $faker->phoneNumber,
                        'check_in' => $checkIn->format('Y-m-d'),
                        'check_out' => $checkOut->format('Y-m-d'),
                        'booked_on' => $bookedOn->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }
}
