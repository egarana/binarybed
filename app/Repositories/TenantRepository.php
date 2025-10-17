<?php

namespace App\Repositories;

use App\Models\Tenant;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Repository untuk query Tenant.
 * Menggunakan Spatie Query Builder untuk filtering, sorting, dan includes.
 */
class TenantRepository
{
    public function all()
    {
        return QueryBuilder::for(Tenant::class)
            ->allowedFilters(['name', 'slug', 'id'])
            ->allowedSorts(['name', 'created_at'])
            ->allowedIncludes(['domains'])
            ->defaultSort('-created_at')
            ->paginate()
            ->appends(request()->query());
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }
}
