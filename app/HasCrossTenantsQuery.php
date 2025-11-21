<?php

namespace App;

use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasCrossTenantsQuery
{
    /**
     * Get all records from all tenant databases using optimized UNION query.
     *
     * Performance: Single database round-trip, database-level filtering & sorting.
     * Best for: Production use, large datasets, read-heavy operations.
     *
     * @param array $filters Optional filters (where conditions)
     * @param array $sorts Optional sorting ['field' => 'direction']
     * @param int|null $limit Optional limit
     * @return array
     */
    public function getAllFromAllTenants(array $filters = [], array $sorts = [], ?int $limit = null): array
    {
        $tenants = Tenant::with('domains')->get();

        if ($tenants->isEmpty()) {
            return [];
        }

        return $this->executeUnionQuery($tenants, $filters, $sorts, $limit);
    }

    /**
     * Execute optimized UNION query across all tenant databases.
     */
    protected function executeUnionQuery(
        $tenants,
        array $filters = [],
        array $sorts = [],
        ?int $limit = null
    ): array {
        $unionQueries = [];
        $bindings = [];

        foreach ($tenants as $tenant) {
            $dbName = config('tenancy.database.prefix') . $tenant->id;

            // Build WHERE clause
            $whereClause = $this->buildWhereClause($filters);

            $query = "
                SELECT
                    units.id,
                    units.name,
                    units.slug,
                    units.created_at,
                    units.updated_at,
                    ? as tenant_id,
                    ? as tenant_name,
                    ? as tenant_domain
                FROM {$dbName}.units
                {$whereClause['sql']}
            ";

            $unionQueries[] = $query;
            $bindings[] = $tenant->id;
            $bindings[] = $tenant->name;
            $bindings[] = $tenant->domain;

            // Add filter bindings
            $bindings = array_merge($bindings, $whereClause['bindings']);
        }

        $sql = implode(' UNION ALL ', $unionQueries);

        // Add ORDER BY
        if (!empty($sorts)) {
            $orderClauses = [];
            foreach ($sorts as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "{$field} {$direction}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY created_at DESC';
        }

        // Add LIMIT
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        try {
            $results = DB::connection(config('tenancy.database.central_connection'))
                ->select($sql, $bindings);

            return array_map(fn($row) => (array) $row, $results);
        } catch (\Exception $e) {
            Log::warning('Failed to execute UNION query, falling back to iterative approach', [
                'error' => $e->getMessage()
            ]);

            return $this->executeFallbackQuery($tenants, $filters, $sorts, $limit);
        }
    }

    /**
     * Fallback method using iterative approach (only used if UNION fails).
     */
    protected function executeFallbackQuery(
        $tenants,
        array $filters = [],
        array $sorts = [],
        ?int $limit = null
    ): array {
        $allUnits = [];

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($tenant, &$allUnits, $filters) {
                    $query = Unit::query();

                    // Apply filters
                    foreach ($filters as $field => $value) {
                        if (is_array($value)) {
                            $query->whereIn($field, $value);
                        } else {
                            $query->where($field, $value);
                        }
                    }

                    $units = $query->get();

                    foreach ($units as $unit) {
                        $allUnits[] = [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'slug' => $unit->slug,
                            'created_at' => $unit->created_at,
                            'updated_at' => $unit->updated_at,
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name,
                            'tenant_domain' => $tenant->domain,
                        ];
                    }
                });
            } catch (\Exception $e) {
                Log::warning('Failed to query tenant database', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        // Apply sorting
        if (!empty($sorts)) {
            $allUnits = collect($allUnits);
            foreach ($sorts as $field => $direction) {
                $allUnits = $direction === 'desc'
                    ? $allUnits->sortByDesc($field)
                    : $allUnits->sortBy($field);
            }
            $allUnits = $allUnits->values()->toArray();
        }

        // Apply limit
        if ($limit && count($allUnits) > $limit) {
            $allUnits = array_slice($allUnits, 0, $limit);
        }

        return $allUnits;
    }

    /**
     * Build WHERE clause for SQL query with parameter binding.
     */
    protected function buildWhereClause(array $filters): array
    {
        if (empty($filters)) {
            return ['sql' => '', 'bindings' => []];
        }

        $conditions = [];
        $bindings = [];

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $conditions[] = "{$field} IN ({$placeholders})";
                $bindings = array_merge($bindings, $value);
            } else {
                $conditions[] = "{$field} = ?";
                $bindings[] = $value;
            }
        }

        $sql = 'WHERE ' . implode(' AND ', $conditions);

        return ['sql' => $sql, 'bindings' => $bindings];
    }
}