<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Events\TenantCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenantAction
{
    /**
     * Jalankan proses pembuatan tenant baru.
     *
     * @param  string  $tenantId
     * @param  string  $tenantName
     * @param  string  $domain
     * @return Tenant
     */
    public function execute(string $tenantId, string $tenantName, string $domain): Tenant
    {
        try {
            // pastikan selalu di koneksi central
            tenancy()->central(function () use ($tenantId, $tenantName, $domain, &$tenant) {

                // 1. Buat tenant
                $tenant = Tenant::create([
                    'id'   => $tenantId,
                    'name' => $tenantName,
                ]);

                // 2. Buat domain (lowercase & trim)
                Domain::create([
                    'domain'    => Str::of($domain)->lower()->trim()->toString(),
                    'tenant_id' => $tenant->id,
                ]);
            });

            // 3. Event di luar context tenancy
            event(new TenantCreated($tenant));

            return $tenant->fresh();
        } catch (\Throwable $e) {
            // rollback manual kalau step 2 gagal
            tenancy()->central(fn () => Tenant::find($tenantId)?->delete());
            throw $e;
        }
    }
}
