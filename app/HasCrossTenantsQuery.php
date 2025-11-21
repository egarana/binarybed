<?php

namespace App;

use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * Get paginated records from all tenant databases using optimized UNION query.
     *
     * Performance: Two database round-trips (count + data), database-level filtering & sorting.
     * Returns: Laravel LengthAwarePaginator instance.
     *
     * @param int $perPage Records per page (default: 15)
     * @param array $filters Optional filters (where conditions)
     * @param array $sorts Optional sorting ['field' => 'direction']
     * @param int|null $page Current page (null = auto-detect from request)
     * @param string $pageName Page parameter name (default: 'page')
     * @return LengthAwarePaginator
     */
    public function getAllFromAllTenantsPaginated(
        int $perPage = 15,
        array $filters = [],
        array $sorts = [],
        ?int $page = null,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $tenants = Tenant::with('domains')->get();

        if ($tenants->isEmpty()) {
            return new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        }

        // Get current page
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

        // Get total count efficiently
        $total = $this->getTotalCountAcrossTenants($tenants, $filters);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated data
        $items = $this->executeUnionQuery($tenants, $filters, $sorts, $perPage, $offset);

        // Return LengthAwarePaginator
        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get total count of records across all tenants with filters.
     *
     * @param $tenants Collection of tenant models
     * @param array $filters Optional filters
     * @return int Total count
     */
    protected function getTotalCountAcrossTenants($tenants, array $filters = []): int
    {
        try {
            $queryParts = $this->buildUnionQueryParts($tenants, $filters, isCount: true);

            if (empty($queryParts['queries'])) {
                return 0;
            }

            $sql = implode(' UNION ALL ', $queryParts['queries']);
            $sql = "SELECT SUM(cnt) as total FROM ({$sql}) as counts";

            $result = DB::connection(config('tenancy.database.central_connection'))
                ->select($sql, $queryParts['bindings']);

            return (int) ($result[0]->total ?? 0);
        } catch (\Exception $e) {
            Log::warning('Failed to execute count query, using fallback', [
                'error' => $e->getMessage()
            ]);

            return $this->getFallbackCount($tenants, $filters);
        }
    }

    /**
     * Build UNION query parts that can be reused for both SELECT and COUNT.
     *
     * @param $tenants Collection of tenant models
     * @param array $filters Optional filters
     * @param bool $isCount Whether to build count query
     * @return array ['queries' => [...], 'bindings' => [...]]
     */
    protected function buildUnionQueryParts($tenants, array $filters = [], bool $isCount = false): array
    {
        $unionQueries = [];
        $bindings = [];

        // Get model class and derive table name
        $modelClass = $this->getCrossTenantsModelClass();
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();

        foreach ($tenants as $tenant) {
            $dbName = config('tenancy.database.prefix') . $tenant->id;
            $whereClause = $this->buildWhereClause($filters);

            if ($isCount) {
                // Count query - no need for column selection or tenant info
                $query = "SELECT COUNT(*) as cnt FROM {$dbName}.{$tableName} {$whereClause['sql']}";
                $unionQueries[] = $query;
                $bindings = array_merge($bindings, $whereClause['bindings']);
            } else {
                // Data query - full column selection with tenant info
                $columns = $this->getCrossTenantsColumns();
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
                $bindings = array_merge($bindings, $whereClause['bindings']);
            }
        }

        return ['queries' => $unionQueries, 'bindings' => $bindings];
    }

    /**
     * Execute optimized UNION query across all tenant databases.
     *
     * @param $tenants Collection of tenant models
     * @param array $filters Optional filters
     * @param array $sorts Optional sorting
     * @param int|null $limit Optional limit
     * @param int $offset Optional offset for pagination
     * @return array
     */
    protected function executeUnionQuery(
        $tenants,
        array $filters = [],
        array $sorts = [],
        ?int $limit = null,
        int $offset = 0
    ): array {
        $queryParts = $this->buildUnionQueryParts($tenants, $filters, isCount: false);

        if (empty($queryParts['queries'])) {
            return [];
        }

        $sql = implode(' UNION ALL ', $queryParts['queries']);

        // Add ORDER BY
        $sql .= $this->buildOrderByClause($sorts);

        // Add LIMIT and OFFSET
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        if ($offset > 0) {
            $sql .= " OFFSET {$offset}";
        }

        try {
            $results = DB::connection(config('tenancy.database.central_connection'))
                ->select($sql, $queryParts['bindings']);

            $results = array_map(fn($row) => (array) $row, $results);

            // Format timestamps to ISO8601 format (same as Eloquent serialization)
            return $this->formatTimestamps($results);
        } catch (\Exception $e) {
            Log::warning('Failed to execute UNION query, falling back to iterative approach', [
                'error' => $e->getMessage()
            ]);

            return $this->executeFallbackQuery($tenants, $filters, $sorts, $limit, $offset);
        }
    }

    /**
     * Fallback method using iterative approach (only used if UNION fails).
     *
     * @param $tenants Collection of tenant models
     * @param array $filters Optional filters
     * @param array $sorts Optional sorting
     * @param int|null $limit Optional limit
     * @param int $offset Optional offset
     * @return array
     */
    protected function executeFallbackQuery(
        $tenants,
        array $filters = [],
        array $sorts = [],
        ?int $limit = null,
        int $offset = 0
    ): array {
        $allRecords = [];
        $modelClass = $this->getCrossTenantsModelClass();
        $columns = $this->getCrossTenantsColumns();

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($tenant, &$allRecords, $filters, $modelClass, $columns) {
                    $query = $modelClass::query();

                    // Apply filters
                    $this->applyFiltersToQuery($query, $filters);

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

        // Apply offset and limit
        if ($offset > 0 || $limit) {
            $allRecords = array_slice(
                $allRecords,
                $offset,
                $limit ?? count($allRecords)
            );
        }

        // Format timestamps to ISO8601 format for consistency
        return $this->formatTimestamps($allRecords);
    }

    /**
     * Get fallback count by iterating through tenants.
     *
     * @param $tenants Collection of tenant models
     * @param array $filters Optional filters
     * @return int
     */
    protected function getFallbackCount($tenants, array $filters = []): int
    {
        $total = 0;
        $modelClass = $this->getCrossTenantsModelClass();

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($tenant, &$total, $filters, $modelClass) {
                    $query = $modelClass::query();
                    $this->applyFiltersToQuery($query, $filters);
                    $total += $query->count();
                });
            } catch (\Exception $e) {
                Log::warning('Failed to count tenant database', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return $total;
    }

    /**
     * Apply filters to an Eloquent query builder.
     *
     * @param $query Query builder instance
     * @param array $filters Filters to apply
     * @return void
     */
    protected function applyFiltersToQuery($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Build ORDER BY clause for SQL query.
     *
     * @param array $sorts Sorting configuration ['field' => 'direction']
     * @return string SQL ORDER BY clause
     */
    protected function buildOrderByClause(array $sorts = []): string
    {
        if (!empty($sorts)) {
            $orderClauses = [];
            foreach ($sorts as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "{$field} {$direction}";
            }
            return ' ORDER BY ' . implode(', ', $orderClauses);
        }

        return ' ORDER BY created_at DESC';
    }

    /**
     * Build WHERE clause for SQL query with parameter binding.
     *
     * @param array $filters Filter configuration
     * @return array ['sql' => string, 'bindings' => array]
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
     *
     * @param array $columns Column names to select
     * @param string $tableName Table name for prefixing
     * @return string SQL column selection clause
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

    /**
     * Format timestamp fields to ISO8601 format (same as Eloquent serialization).
     *
     * @param array $records Array of records to format
     * @return array Records with formatted timestamps
     */
    protected function formatTimestamps(array $records): array
    {
        // Common timestamp field names in Laravel
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($records as &$record) {
            foreach ($timestampFields as $field) {
                if (isset($record[$field]) && $record[$field] !== null) {
                    try {
                        // Parse MySQL datetime format and convert to ISO8601 with microseconds
                        // Format: 2025-11-21T09:11:42.000000Z
                        $date = \Carbon\Carbon::parse($record[$field]);
                        $record[$field] = $date->format('Y-m-d\TH:i:s.u\Z');
                    } catch (\Exception $e) {
                        // If parsing fails, keep original value
                        Log::warning('Failed to format timestamp', [
                            'field' => $field,
                            'value' => $record[$field],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        return $records;
    }
}