<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

/**
 * UpdateTenantAction
 * ------------------
 * Bertanggung jawab untuk update tenant dan domain secara atomic (1 transaksi)
 */
class UpdateTenant
{
    /**
     * Jalankan aksi update tenant
     */
    public function execute(Tenant $tenant, array $data): Tenant
    {
        return DB::transaction(function () use ($tenant, $data) {
            // Update nama tenant jika tersedia
            $tenant->update([
                'name' => $data['tenant_name'] ?? $tenant->name,
            ]);

            // Update atau buat domain baru jika domain disediakan
            if (isset($data['domain'])) {
                $domain = $tenant->domains()->first();

                $domain
                    ? $domain->update(['domain' => Str::lower($data['domain'])])
                    : Domain::create([
                        'domain'    => Str::lower($data['domain']),
                        'tenant_id' => $tenant->id,
                    ]);
            }

            return $tenant->fresh();
        });
    }
}
