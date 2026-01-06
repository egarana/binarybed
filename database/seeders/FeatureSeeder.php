<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                "name" => "Hotel Transfer",
                "value" => "hotel-transfer",
                "description" => "Private pick-up and drop-off (AC car)",
                "icon" => null
            ],
            [
                "name" => "Entrance Fees",
                "value" => "entrance-fees",
                "description" => "All entrance tickets to Kintamani & Mount Batur area",
                "icon" => null
            ],
            [
                "name" => "Professional Guide",
                "value" => "professional-guide",
                "description" => "Licensed English-speaking local trekking guide",
                "icon" => null
            ],
            [
                "name" => "Safety Equipment",
                "value" => "safety-equipment",
                "description" => "Flashlight or headlamp & trekking pole",
                "icon" => null
            ],
            [
                "name" => "Breakfast",
                "value" => "breakfast",
                "description" => "Light breakfast provided (Sandwich, Egg, etc)",
                "icon" => null
            ],
            [
                "name" => "Hot Beverages",
                "value" => "hot-beverages",
                "description" => "Hot coffee, tea, or hot chocolate",
                "icon" => null
            ],
            [
                "name" => "Mineral Water",
                "value" => "mineral-water",
                "description" => "Bottled mineral water",
                "icon" => null
            ],
            [
                "name" => "Warm Clothing",
                "value" => "warm-clothing",
                "description" => "Jacket, long pants, or hiking shoes",
                "icon" => null
            ],
            [
                "name" => "Travel Insurance",
                "value" => "travel-insurance",
                "description" => "Personal travel insurance",
                "icon" => null
            ],
            [
                "name" => "Tipping",
                "value" => "tipping",
                "description" => "Gratuities for the guide and driver",
                "icon" => null
            ],
            [
                "name" => "Personal Expenses",
                "value" => "personal-expenses",
                "description" => "Souvenirs or extra snacks/drinks",
                "icon" => null
            ],
            [
                "name" => "Hot Spring Ticket",
                "value" => "hot-spring-ticket",
                "description" => "Hot spring ticket access",
                "icon" => null
            ],
            [
                "name" => "Hiking Shoes",
                "value" => "hiking-shoes",
                "description" => "Shoes with good grip",
                "icon" => null
            ],
            [
                "name" => "Camera",
                "value" => "camera",
                "description" => "Camera or smartphone for pictures",
                "icon" => null
            ],
            [
                "name" => "Sunscreen",
                "value" => "sunscreen",
                "description" => "Sunscreen and sunglasses",
                "icon" => null
            ],
            [
                "name" => "Cash",
                "value" => "cash",
                "description" => "Cash (IDR) for tips or extra expenses",
                "icon" => null
            ],
            [
                "name" => "Bidet",
                "value" => "bidet",
                "description" => "Bidet installed in bathroom",
                "icon" => null
            ],
            [
                "name" => "Hot Water",
                "value" => "hot-water",
                "description" => "Hot water shower available",
                "icon" => null
            ],
            [
                "name" => "TV",
                "value" => "tv",
                "description" => "Television for entertainment",
                "icon" => null
            ],
            [
                "name" => "First Aid Kit",
                "value" => "first-aid-kit",
                "description" => "First aid kit available for safety",
                "icon" => null
            ],
            [
                "name" => "Wifi",
                "value" => "wifi",
                "description" => "High-speed wireless internet",
                "icon" => null
            ],
            [
                "name" => "Dedicated Workspace",
                "value" => "dedicated-workspace",
                "description" => "A desk or table with a chair for working",
                "icon" => null
            ],
            [
                "name" => "Kitchen",
                "value" => "kitchen",
                "description" => "Space where guests can cook their own meals",
                "icon" => null
            ],
            [
                "name" => "Barbecue Utensils",
                "value" => "barbecue-utensils",
                "description" => "Grill, charcoal, bamboo skewers/iron skewers, etc.",
                "icon" => null
            ],
            [
                "name" => "Dining Table",
                "value" => "dining-table",
                "description" => "Dining table provided",
                "icon" => null
            ],
            [
                "name" => "Coffee",
                "value" => "coffee",
                "description" => "Coffee maker or supplies",
                "icon" => null
            ],
            [
                "name" => "Lake Access",
                "value" => "lake-access",
                "description" => "Guests can get to a lake using a path or dock",
                "icon" => null
            ],
            [
                "name" => "Patio or Balcony",
                "value" => "patio-balcony",
                "description" => "Outdoor patio or balcony area",
                "icon" => null
            ],
            [
                "name" => "BBQ Grill",
                "value" => "bbq-grill",
                "description" => "Outdoor BBQ grill available",
                "icon" => null
            ],
            [
                "name" => "Free Parking",
                "value" => "free-parking",
                "description" => "Free parking on premises",
                "icon" => null
            ],
            [
                "name" => "Street Parking",
                "value" => "street-parking",
                "description" => "Free street parking nearby",
                "icon" => null
            ],
            [
                "name" => "Pets Allowed",
                "value" => "pets-allowed",
                "description" => "Assistance animals are always allowed",
                "icon" => null
            ],
            [
                "name" => "Smoking Allowed",
                "value" => "smoking-allowed",
                "description" => "Smoking is allowed in designated areas",
                "icon" => null
            ],
            [
                "name" => "Self Check-in",
                "value" => "self-check-in",
                "description" => "Self check-in with keybox or smartlock",
                "icon" => null
            ],
            [
                "name" => "Building Staff",
                "value" => "building-staff",
                "description" => "Someone is available 24 hours a day to let guests in",
                "icon" => null
            ],
            // Not included / Unavailable features (can be used for exclusions)
            [
                "name" => "Exterior Security Cameras",
                "value" => "exterior-security-cameras",
                "description" => "Security cameras on property exterior",
                "icon" => null
            ],
            [
                "name" => "Washer",
                "value" => "washer",
                "description" => "Washing machine available",
                "icon" => null
            ],
            [
                "name" => "Dryer",
                "value" => "dryer",
                "description" => "Clothes dryer available",
                "icon" => null
            ],
            [
                "name" => "Air Conditioning",
                "value" => "air-conditioning",
                "description" => "Air conditioning unit available",
                "icon" => null
            ],
            [
                "name" => "Essentials",
                "value" => "essentials",
                "description" => "Towels, bed sheets, soap, and toilet paper",
                "icon" => null
            ],
            [
                "name" => "Smoke Alarm",
                "value" => "smoke-alarm",
                "description" => "Smoke detector installed on property",
                "icon" => null
            ],
            [
                "name" => "Carbon Monoxide Alarm",
                "value" => "carbon-monoxide-alarm",
                "description" => "Carbon monoxide detector installed",
                "icon" => null
            ],
            [
                "name" => "Heating",
                "value" => "heating",
                "description" => "Heating system available",
                "icon" => null
            ],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['value' => $feature['value']],
                $feature
            );
        }

        $this->command->info('Features seeded successfully!');
    }
}
