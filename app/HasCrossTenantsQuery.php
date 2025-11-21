<?php

namespace App;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasCrossTenantsQuery
{
    /**
     * Get the model class to query across tenants.
     * This method must be implemented by the class using this trait.
     *
     * @return string The fully qualified model class name
     */
    abstract protected function getCrossTenantsModelClass(): string;

    /**
     * Get the columns to select from the model.
     * Override this method to customize columns.
     *
     * @return array Array of column names to select
     */
    protected function getCrossTenantsColumns(): array
    {
        // Default: select common columns
        // Classes can override this to specify their own columns
        return ['*'];
    }

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

        // Get model class and derive table name
        $modelClass = $this->getCrossTenantsModelClass();
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();

        // Get columns to select
        $columns = $this->getCrossTenantsColumns();

        foreach ($tenants as $tenant) {
            $dbName = config('tenancy.database.prefix') . $tenant->id;

            // Build WHERE clause
            $whereClause = $this->buildWhereClause($filters);

            // Build column selection
            $selectColumns = $this->buildSelectColumns($columns, $tableName);

            $query = "
                SELECT
                    {$selectColumns},
                    ? as tenant_id,
                    ? as tenant_name,
                    ? as tenant_domain
                FROM {$dbName}.{$tableName}
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
        $allRecords = [];
        $modelClass = $this->getCrossTenantsModelClass();
        $columns = $this->getCrossTenantsColumns();

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($tenant, &$allRecords, $filters, $modelClass, $columns) {
                    $query = $modelClass::query();

                    // Apply filters
                    foreach ($filters as $field => $value) {
                        if (is_array($value)) {
                            $query->whereIn($field, $value);
                        } else {
                            $query->where($field, $value);
                        }
                    }

                    // Apply column selection if not using wildcard
                    if (!in_array('*', $columns)) {
                        $query->select($columns);
                    }

                    $records = $query->get();

                    foreach ($records as $record) {
                        $recordArray = $record->toArray();
                        $recordArray['tenant_id'] = $tenant->id;
                        $recordArray['tenant_name'] = $tenant->name;
                        $recordArray['tenant_domain'] = $tenant->domain;

                        $allRecords[] = $recordArray;
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
            $allRecords = collect($allRecords);
            foreach ($sorts as $field => $direction) {
                $allRecords = $direction === 'desc'
                    ? $allRecords->sortByDesc($field)
                    : $allRecords->sortBy($field);
            }
            $allRecords = $allRecords->values()->toArray();
        }

        // Apply limit
        if ($limit && count($allRecords) > $limit) {
            $allRecords = array_slice($allRecords, 0, $limit);
        }

        return $allRecords;
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

    /**
     * Build SELECT columns clause for SQL query.
     */
    protected function buildSelectColumns(array $columns, string $tableName): string
    {
        if (in_array('*', $columns)) {
            return "{$tableName}.*";
        }

        $selectColumns = [];
        foreach ($columns as $column) {
            $selectColumns[] = "{$tableName}.{$column}";
        }

        return implode(', ', $selectColumns);
    }
}