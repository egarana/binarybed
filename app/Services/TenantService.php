<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use App\Actions\CreateTenantAction;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

/**
 * TenantService
 * -------------
 * Layer untuk koordinasi logika bisnis tenant.
 * - Tidak menyentuh database langsung
 * - Menggunakan Action class untuk operasi atomic
 */
class TenantService
{
    protected CreateTenantAction $createTenantAction;

    public function __construct(CreateTenantAction $createTenantAction)
    {
        $this->createTenantAction = $createTenantAction;
    }

    /**
     * Membuat tenant baru (delegasi ke Action)
     */
    public function create(array $data): Tenant
    {
        return $this->createTenantAction->execute(
            $data['id'],
            $data['name'],
            $data['domain']
        );
    }

    /**
     * Update data tenant & domain
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        return DB::transaction(function () use ($tenant, $data) {
            $tenant->update(['name' => $data['tenant_name'] ?? $tenant->name]);

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

    /**
     * Hapus tenant beserta domainnya
     */
    public function delete(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $tenant->domains()->delete();
            $tenant->delete();
        });
    }
}
