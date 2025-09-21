<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('features')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $features = [
            [
                "name"  => "24/7 reception",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M3 20a1 1 0 0 1-1-1v-1a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v1a1 1 0 0 1-1 1Z\"/><path d=\"M20 16a8 8 0 1 0-16 0\"/><path d=\"M12 4v4\"/><path d=\"M10 4h4\"/></svg>",
                "value" => "24_7_reception"
            ],
            [
                "name"  => "Airport shuttle",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z\"/></svg>",
                "value" => "airport_shuttle"
            ],
            [
                "name"  => "Assistance animals allowed",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M11.25 16.25h1.5L12 17z\"/><path d=\"M16 14v.5\"/><path d=\"M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309\"/><path d=\"M8 14v.5\"/><path d=\"M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5.277c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5\"/></svg>",
                "value" => "assistance_animals_allowed"
            ],
            [
                "name"  => "BBQ utensils",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M16.4 13.7A6.5 6.5 0 1 0 6.28 6.6c-1.1 3.13-.78 3.9-3.18 6.08A3 3 0 0 0 5 18c4 0 8.4-1.8 11.4-4.3\"/><path d=\"m18.5 6 2.19 4.5a6.48 6.48 0 0 1-2.29 7.2C15.4 20.2 11 22 7 22a3 3 0 0 1-2.68-1.66L2.4 16.5\"/><circle cx=\"12.5\" cy=\"8.5\" r=\"2.5\"/></svg>",
                "value" => "bbq_utensils"
            ],
            [
                "name"  => "Bicycle rental",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"18.5\" cy=\"17.5\" r=\"3.5\"/><circle cx=\"5.5\" cy=\"17.5\" r=\"3.5\"/><circle cx=\"15\" cy=\"5\" r=\"1\"/><path d=\"M12 17.5V14l-3-3 4-3 2 3h2\"/></svg>",
                "value" => "bicycle_rental"
            ],
            [
                "name"  => "Bidet",
                "icon"  => null,
                "value" => "bidet"
            ],
            [
                "name"  => "Bike tours",
                "icon"  => null,
                "value" => "bike_tours"
            ],
            [
                "name"  => "Breakfast",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2\"/><path d=\"M7 2v20\"/><path d=\"M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7\"/></svg>",
                "value" => "breakfast"
            ],
            [
                "name"  => "Car rental",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2\"/><circle cx=\"7\" cy=\"17\" r=\"2\"/><path d=\"M9 17h6\"/><circle cx=\"17\" cy=\"17\" r=\"2\"/></svg>",
                "value" => "car_rental"
            ],
            [
                "name"  => "Coffee",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M10 2v2\"/><path d=\"M14 2v2\"/><path d=\"M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1\"/><path d=\"M6 2v2\"/></svg>",
                "value" => "coffee"
            ],
            [
                "name"  => "Cycling (off-site)",
                "icon"  => null,
                "value" => "cycling_off_site"
            ],
            [
                "name"  => "Daily housekeeping",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m16 22-1-4\"/><path d=\"M19 13.99a1 1 0 0 0 1-1V12a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v.99a1 1 0 0 0 1 1\"/><path d=\"M5 14h14l1.973 6.767A1 1 0 0 1 20 22H4a1 1 0 0 1-.973-1.233z\"/><path d=\"m8 22 1-4\"/></svg>",
                "value" => "daily_housekeeping"
            ],
            [
                "name"  => "Dedicated workspace",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 12h.01\"/><path d=\"M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2\"/><path d=\"M22 13a18.15 18.15 0 0 1-20 0\"/><rect width=\"20\" height=\"14\" x=\"2\" y=\"6\" rx=\"2\"/></svg>",
                "value" => "dedicated_workspace"
            ],
            [
                "name"  => "Dining table",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2\"/><path d=\"M7 2v20\"/><path d=\"M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7\"/></svg>",
                "value" => "dining_table"
            ],
            [
                "name"  => "Fishing (off-site)",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\">><path d=\"M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-.94 3.47-3.44 6-7 6s-7.56-2.53-8.5-6Z\"/><path d=\"M18 12v.5\"/><path d=\"M16 17.93a9.77 9.77 0 0 1 0-11.86\"/><path d=\"M7 10.67C7 8 5.58 5.97 2.73 5.5c-1 1.5-1 5 .23 6.5-1.24 1.5-1.24 5-.23 6.5C5.58 18.03 7 16 7 13.33\"/><path d=\"M10.46 7.26C10.2 5.88 9.17 4.24 8 3h5.8a2 2 0 0 1 1.98 1.67l.23 1.4\"/><path d=\"m16.01 17.93-.23 1.4A2 2 0 0 1 13.8 21H9.5a5.96 5.96 0 0 0 1.49-3.98\"/></svg>",
                "value" => "fishing_off_site"
            ],
            [
                "name"  => "First aid kit",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 11v4\"/><path d=\"M14 13h-4\"/><path d=\"M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2\"/><path d=\"M18 6v14\"/><path d=\"M6 6v14\"/><rect width=\"20\" height=\"14\" x=\"2\" y=\"6\" rx=\"2\"/></svg>",
                "value" => "first_aid_kit"
            ],
            [
                "name"  => "Free parking",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M9 17V7h4a3 3 0 0 1 0 6H9\"/></svg>",
                "value" => "free_parking"
            ],
            [
                "name"  => "Free Wifi",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 20h.01\"/><path d=\"M2 8.82a15 15 0 0 1 20 0\"/><path d=\"M5 12.859a10 10 0 0 1 14 0\"/><path d=\"M8.5 16.429a5 5 0 0 1 7 0\"/></svg>",
                "value" => "free_wifi"
            ],
            [
                "name"  => "Fruit (additional charge)",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 6.528V3a1 1 0 0 1 1-1h0\"/><path d=\"M18.237 21A15 15 0 0 0 22 11a6 6 0 0 0-10-4.472A6 6 0 0 0 2 11a15.1 15.1 0 0 0 3.763 10 3 3 0 0 0 3.648.648 5.5 5.5 0 0 1 5.178 0A3 3 0 0 0 18.237 21\"/></svg>",
                "value" => "fruit"
            ],
            [
                "name"  => "Garden",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 5a3 3 0 1 1 3 3m-3-3a3 3 0 1 0-3 3m3-3v1M9 8a3 3 0 1 0 3 3M9 8h1m5 0a3 3 0 1 1-3 3m3-3h-1m-2 3v-1\"/><circle cx=\"12\" cy=\"8\" r=\"2\"/><path d=\"M12 10v12\"/><path d=\"M12 22c4.2 0 7-1.667 7-5-4.2 0-7 1.667-7 5Z\"/><path d=\"M12 22c-4.2 0-7-1.667-7-5 4.2 0 7 1.667 7 5Z\"/></svg>",
                "value" => "garden"
            ],
            [
                "name"  => "Guided cultural tours",
                "icon"  => null,
                "value" => "cultural_tours"
            ],
            [
                "name"  => "Hiking (off-site)",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m8 3 4 8 5-5 5 15H2L8 3z\"/></svg>",
                "value" => "hiking_off_site"
            ],
            [
                "name"  => "Hot water",
                "icon"  => null,
                "value" => "hot_water"
            ],
            [
                "name"  => "Ironing service",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z\"/></svg>",
                "value" => "ironing_service"
            ],
            [
                "name"  => "Kitchen",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M2 12h20\"/><path d=\"M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8\"/><path d=\"m4 8 16-4\"/><path d=\"m8.86 6.78-.45-1.81a2 2 0 0 1 1.45-2.43l1.94-.48a2 2 0 0 1 2.43 1.46l.45 1.8\"/></svg>",
                "value" => "kitchen"
            ],
            [
                "name"  => "Lake access",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M4 16v-2.38C4 11.5 2.97 10.5 3 8c.03-2.72 1.49-6 4.5-6C9.37 2 10 3.8 10 5.5c0 3.11-2 5.66-2 8.68V16a2 2 0 1 1-4 0Z\"/><path d=\"M20 20v-2.38c0-2.12 1.03-3.12 1-5.62-.03-2.72-1.49-6-4.5-6C14.63 6 14 7.8 14 9.5c0 3.11 2 5.66 2 8.68V20a2 2 0 1 0 4 0Z\"/><path d=\"M16 17h4\"/><path d=\"M4 13h4\"/></svg>",
                "value" => "lake_access"
            ],
            [
                "name"  => "Non-smoking rooms",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 12H3a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h13\"/><path d=\"M18 8c0-2.5-2-2.5-2-5\"/><path d=\"m2 2 20 20\"/><path d=\"M21 12a1 1 0 0 1 1 1v2a1 1 0 0 1-.5.866\"/><path d=\"M22 8c0-2.5-2-2.5-2-5\"/><path d=\"M7 12v4\"/></svg>",
                "value" => "non_smoking_rooms"
            ],
            [
                "name"  => "Outdoor furniture",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"3.5 2 6.5 12.5 18 12.5\"/><line x1=\"9.5\" x2=\"5.5\" y1=\"12.5\" y2=\"20\"/><line x1=\"15\" x2=\"18.5\" y1=\"12.5\" y2=\"20\"/><path d=\"M2.75 18a13 13 0 0 0 18.5 0\"/></svg>",
                "value" => "outdoor_furniture"
            ],
            [
                "name"  => "Patio or balcony",
                "icon"  => null,
                "value" => "patio_or_balcony"
            ],
            [
                "name"  => "Pets allowed",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"11\" cy=\"4\" r=\"2\"/><circle cx=\"18\" cy=\"8\" r=\"2\"/><circle cx=\"20\" cy=\"16\" r=\"2\"/><path d=\"M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z\"/></svg>",
                "value" => "pets_allowed"
            ],
            [
                "name"  => "Picnic area",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m15 11-1 9\"/><path d=\"m19 11-4-7\"/><path d=\"M2 11h20\"/><path d=\"m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4\"/><path d=\"M4.5 15.5h15\"/><path d=\"m5 11 4-7\"/><path d=\"m9 11 1 9\"/></svg>",
                "value" => "picnic_area"
            ],
            [
                "name"  => "Private bathroom",
                "icon"  => null,
                "value" => "private_bathroom"
            ],
            [
                "name"  => "Room service",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 3V2\"/><path d=\"m15.4 17.4 3.2-2.8a2 2 0 1 1 2.8 2.9l-3.6 3.3c-.7.8-1.7 1.2-2.8 1.2h-4c-1.1 0-2.1-.4-2.8-1.2l-1.302-1.464A1 1 0 0 0 6.151 19H5\"/><path d=\"M2 14h12a2 2 0 0 1 0 4h-2\"/><path d=\"M4 10h16\"/><path d=\"M5 10a7 7 0 0 1 14 0\"/><path d=\"M5 14v6a1 1 0 0 1-1 1H2\"/></svg>",
                "value" => "room_service"
            ],
            [
                "name"  => "Self check-in",
                "icon"  => null,
                "value" => "self_check_in"
            ],
            [
                "name"  => "Terrace",
                "icon"  => null,
                "value" => "terrace"
            ],
            [
                "name"  => "TV",
                "icon"  => "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m17 2-5 5-5-5\"/><rect width=\"20\" height=\"15\" x=\"2\" y=\"7\" rx=\"2\"/></svg>",
                "value" => "tv"
            ]
        ];

        DB::table('features')->insert($features);
    }
}
