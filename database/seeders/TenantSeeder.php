<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class TenantSeeder extends Seeder
{
    /**
     * Seeder ini hanya mencatat tenant & domain di central database.
     * Tidak membuat database tenant dan tidak memicu event apa pun.
     * Field 'slug' juga otomatis terisi.
     */
    public function run(): void
    {
        // Daftar nama tenant realistis dan khas Bali
        $tenantNames = [
            'Lake Batur Cabin',
            'Natural View Sidemen',
            'Rimba Luna Villas',
            'Sunset Bay Retreat',
            'Ocean Breeze Cottages',
            'Tropical Haven Resort',
            'Bamboo Hill Lodge',
            'Hidden Valley Stay',
            'Ubud Serenity Villa',
            'Coconut Grove Inn',
            'Mountain Edge Cabin',
            'Jungle Whisper Bungalows',
            'Blue Horizon Villa',
            'Palm River Retreat',
            'Lotus Garden Cottages',
            'Balangan Cliff House',
            'Sunkissed Retreat',
            'Ricefield Escape',
            'Coral Reef Bungalows',
            'Bali Sunrise Lodge',
            'Emerald Hills Villa',
            'The Bamboo Nest',
            'Tirta Awana Resort',
            'Sidemen Bliss Retreat',
            'Villa Cempaka View',
            'Ayodya Hills Stay',
            'Savana Ridge Lodge',
            'Cempaka River Cottage',
            'Uluwatu Hidden Cabin',
            'White Sand Retreat',
            'The Jungle Garden',
            'Seroja Cliff Lodge',
            'Nusa Haven Villas',
            'Lakeview Bamboo House',
            'Villa Matahari',
            'Bali Breeze Cabin',
            'Awan Eco Retreat',
            'Tegal Serenity Stay',
            'The Banyan Escape',
            'Pondok Indah Hills',
            'Golden Leaf Resort',
            'Canggu Lagoon Villa',
            'Amed Blue Bay Stay',
            'Dewi Lake Cabin',
            'Tirta Bamboo House',
            'Villa Aruna Vista',
            'Alas Garden Cottage',
            'Nirwana Cliff Retreat',
            'Green Valley Stay',
            'Bali Eco Haven',
        ];

        // Jalankan di central database agar tidak trigger tenant context
        tenancy()->central(function () use ($tenantNames) {
            // Nonaktifkan event agar tidak bikin DB per tenant
            Tenant::withoutEvents(function () use ($tenantNames) {
                foreach ($tenantNames as $name) {
                    // tenant_id huruf kecil tanpa spasi
                    $tenantId = strtolower(str_replace(' ', '', $name));
                    // slug format SEO friendly
                    $slug = Str::slug($name);
                    // domain sesuai environment (default: .test)
                    $domain = "{$tenantId}.test";

                    // Skip jika tenant sudah ada
                    if (Tenant::find($tenantId)) {
                        echo "⚠️ Tenant '{$name}' already exists, skipped.\n";
                        continue;
                    }

                    // Catat tenant ke central DB
                    $tenant = Tenant::create([
                        'id'   => $tenantId,
                        'name' => $name,
                        'slug' => $slug,
                    ]);

                    // Catat domain-nya
                    Domain::create([
                        'domain'    => $domain,
                        'tenant_id' => $tenant->id,
                    ]);
                }
            });
        });
    }
}
