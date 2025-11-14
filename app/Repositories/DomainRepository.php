<?php

namespace App\Repositories;

use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

class DomainRepository
{
    /**
     * Create a new domain for a tenant.
     */
    public function create(string $domain, string $tenantId): Domain
    {
        return Domain::create([
            'domain' => Str::of($domain)->lower()->trim()->toString(),
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * Update an existing domain.
     */
    public function update(Domain $domain, string $newDomain): Domain
    {
        $domain->update([
            'domain' => Str::lower($newDomain),
        ]);

        return $domain->fresh();
    }

    /**
     * Delete all domains for a tenant.
     */
    public function deleteByTenantId(string $tenantId): void
    {
        Domain::where('tenant_id', $tenantId)->delete();
    }

    /**
     * Find first domain by tenant ID.
     */
    public function findFirstByTenantId(string $tenantId): ?Domain
    {
        return Domain::where('tenant_id', $tenantId)->first();
    }
}
