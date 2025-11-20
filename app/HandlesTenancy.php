<?php

namespace App;

use App\Models\Tenant;

trait HandlesTenancy
{
    /**
     * Execute a callback within a tenant's database context.
     *
     * @param string $tenantId
     * @param callable $callback
     * @return mixed
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function executeInTenantContext(string $tenantId, callable $callback): mixed
    {
        // Ambil tenant dari central dulu
        $tenant = Tenant::findOrFail($tenantId);
        
        // Jalankan callback di tenant context
        return $tenant->run($callback);
    }
}