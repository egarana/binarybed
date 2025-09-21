<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            [
                'name' => 'Lake Batur Cabin',
                'domain' => 'localhost',
            ],
            [
                'name' => 'Lux Villa Ubud',
                'domain' => 'luxvillaubud.com',
            ],
            [
                'name' => 'Jungle Retreat Bali',
                'domain' => 'jungleretreatbali.com',
            ],
        ];

        foreach ($properties as $data) {
            Property::updateOrCreate(
                ['domain' => $data['domain']],
                ['name' => $data['name']]
            );
        }
    }
}
