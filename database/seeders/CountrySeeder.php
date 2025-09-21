<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Pranpegu\LaravelCountries\Countries;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('countries')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $countries = Countries::all();

        $insertData = collect($countries)->map(function ($country) {
            return [
                'iso2'      => $country['code'],        // e.g. "ID"
                'name'      => $country['name'],        // e.g. "Indonesia"
                'dial_code' => '+' . $country['dial_code'], // e.g. "+62"
            ];
        })->toArray();

        DB::table('countries')->insert($insertData);
    }
}
