<?php

namespace App\Services;

use App\Actions\Units\CreateUnit;
use App\Actions\Units\DeleteUnit;
use App\Actions\Units\FindUnitByTenantAndSlug;
use App\Actions\Units\UpdateUnit;
use App\Models\Tenant;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitService
{
    public function __construct(
        protected UnitRepository $unitRepository,
        protected CreateUnit $createUnit,
        protected UpdateUnit $updateUnit,
        protected DeleteUnit $deleteUnit,
        protected FindUnitByTenantAndSlug $findUnitByTenantAndSlug
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->unitRepository->getAllPaginated($request);
    }

    public function getAllFromAllTenants(): array
    {
        // return $this->getAllFromAllTenantsWithQueryBuilder(
        //     table: 'units',
        //     selectFields: ['id', 'name', 'slug', 'created_at', 'updated_at'],
        //     tenantFields: ['tenant_id' => 'id', 'tenant_name' => 'name', 'tenant_domain' => 'domain'],
        //     allowedFilters: [
        //         'name',
        //         'slug',
        //         AllowedFilter::exact('tenant_id'),
        //         AllowedFilter::partial('tenant_name'),
        //     ],
        //     allowedSorts: ['name', 'created_at', 'updated_at', 'tenant_name'],
        //     allowedIncludes: [], // Empty karena UNION tidak support includes
        //     defaultSort: '-created_at'
        // );
    }

    // public function getAllFromAllTenants(): array
    // {
    //     // Ambil semua tenant dari central database
    //     $tenants = Tenant::with('domains')->get();

    //     if ($tenants->isEmpty()) {
    //         return [];
    //     }

    //     // Build UNION query untuk menggabungkan semua tenant databases
    //     $unionQueries = [];
    //     $bindings = [];

    //     foreach ($tenants as $tenant) {
    //         $dbName = config('tenancy.database.prefix') . $tenant->id;

    //         // Query untuk setiap tenant database
    //         // Tambahkan tenant info langsung di SELECT
    //         $unionQueries[] = "
    //             SELECT
    //                 units.id,
    //                 units.name,
    //                 units.slug,
    //                 units.created_at,
    //                 units.updated_at,
    //                 ? as tenant_id,
    //                 ? as tenant_name,
    //                 ? as tenant_domain
    //             FROM {$dbName}.units
    //         ";

    //         $bindings[] = $tenant->id;
    //         $bindings[] = $tenant->name;
    //         $bindings[] = $tenant->domain;
    //     }

    //     // Gabungkan semua queries dengan UNION ALL
    //     $sql = implode(' UNION ALL ', $unionQueries);

    //     // Tambahkan ORDER BY di akhir untuk sorting
    //     $sql .= ' ORDER BY created_at DESC';

    //     try {
    //         // Execute query menggunakan central connection
    //         $results = DB::connection(config('tenancy.database.central_connection'))
    //             ->select($sql, $bindings);

    //         // Convert stdClass to array
    //         return array_map(fn($row) => (array) $row, $results);
    //     } catch (\Exception $e) {
    //         // Fallback ke method lama jika ada error (misalnya database tidak support UNION atau ada issue lain)
    //         Log::warning('Failed to use UNION query for getAllFromAllTenants, falling back to loop method', [
    //             'error' => $e->getMessage()
    //         ]);

    //         return $this->getAllFromAllTenantsFallback($tenants);
    //     }
    // }

    // protected function getAllFromAllTenantsFallback($tenants): array
    // {
    //     $allUnits = [];

    //     foreach ($tenants as $tenant) {
    //         $tenant->run(function () use ($tenant, &$allUnits) {
    //             $units = Unit::all();

    //             foreach ($units as $unit) {
    //                 $allUnits[] = [
    //                     'id' => $unit->id,
    //                     'name' => $unit->name,
    //                     'slug' => $unit->slug,
    //                     'created_at' => $unit->created_at,
    //                     'updated_at' => $unit->updated_at,
    //                     'tenant_id' => $tenant->id,
    //                     'tenant_name' => $tenant->name,
    //                     'tenant_domain' => $tenant->domain,
    //                 ];
    //             }
    //         });
    //     }

    //     return $allUnits;
    // }

    public function findByTenantAndSlug(string $tenantId, string $slug): array
    {
        return $this->findUnitByTenantAndSlug->execute($tenantId, $slug);
    }

    public function create(array $data): Unit
    {
        return $this->createUnit->execute($data);
    }

    public function update(string $tenantId, string $slug, array $data): Unit
    {
        return $this->updateUnit->execute($tenantId, $slug, $data);
    }

    public function delete(string $tenantId, string $slug): void
    {
        $this->deleteUnit->execute($tenantId, $slug);
    }
}
