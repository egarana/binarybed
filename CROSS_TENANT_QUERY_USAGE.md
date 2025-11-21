# HasCrossTenantsQuery Trait - Usage Guide

The `HasCrossTenantsQuery` trait allows any model to query data across all tenants efficiently using optimized UNION queries with support for **pagination**, filtering, and sorting.

## Features

- **Laravel Pagination**: Returns standard `LengthAwarePaginator` instance
- **Optimized UNION Queries**: Single database round-trip for data, separate efficient count query
- **Database-level Operations**: Filtering, sorting, and pagination at database level (not PHP)
- **Automatic Fallback**: Gracefully degrades to iterative approach if UNION fails
- **Zero Redundancy**: Shared helper methods eliminate code duplication

## How to Use

### 1. Basic Setup

Add the trait to your service class and implement the required `getCrossTenantsModelClass()` method:

```php
<?php

namespace App\Services;

use App\HasCrossTenantsQuery;
use App\Models\Activity;

class ActivityService
{
    use HasCrossTenantsQuery;

    /**
     * Required: Specify which model to query.
     */
    protected function getCrossTenantsModelClass(): string
    {
        return Activity::class;
    }
}
```

### 2. Optional: Customize Columns

By default, the trait selects all columns (`*`). You can override `getCrossTenantsColumns()` to specify which columns to select:

```php
protected function getCrossTenantsColumns(): array
{
    return ['id', 'name', 'status', 'created_at', 'updated_at'];
}
```

Or keep it simple and select all columns:

```php
protected function getCrossTenantsColumns(): array
{
    return ['*']; // This is the default
}
```

### 3. Query Data Across All Tenants

#### Option A: With Pagination (Recommended)

Use the `getAllFromAllTenantsPaginated()` method for paginated results:

```php
// Basic pagination (15 items per page, auto-detect current page)
$units = $unitService->getAllFromAllTenantsPaginated();

// Custom items per page
$units = $unitService->getAllFromAllTenantsPaginated(perPage: 25);

// With filters
$units = $unitService->getAllFromAllTenantsPaginated(
    perPage: 20,
    filters: ['status' => 'active']
);

// With sorting
$units = $unitService->getAllFromAllTenantsPaginated(
    perPage: 20,
    filters: [],
    sorts: ['created_at' => 'desc', 'name' => 'asc']
);

// Complete example with all options
$units = $unitService->getAllFromAllTenantsPaginated(
    perPage: 25,
    filters: ['status' => 'active', 'type' => ['residential', 'commercial']],
    sorts: ['created_at' => 'desc']
);

// Access pagination data (standard Laravel paginator)
$units->total();        // Total records
$units->currentPage();  // Current page number
$units->lastPage();     // Last page number
$units->hasMorePages(); // Has more pages?
$units->items();        // Current page items
$units->links();        // Pagination links (for Blade)

// In controller, return as JSON
return response()->json($units);
```

#### Option B: Without Pagination

Use the `getAllFromAllTenants()` method for simple array results:

```php
// Get all activities from all tenants
$activities = $activityService->getAllFromAllTenants();

// With filters
$activities = $activityService->getAllFromAllTenants([
    'status' => 'active',
    'type' => ['meeting', 'call']
]);

// With sorting
$activities = $activityService->getAllFromAllTenants(
    filters: [],
    sorts: ['created_at' => 'desc', 'name' => 'asc']
);

// With limit
$activities = $activityService->getAllFromAllTenants(
    filters: [],
    sorts: [],
    limit: 100
);

// Combined
$activities = $activityService->getAllFromAllTenants(
    filters: ['status' => 'active'],
    sorts: ['created_at' => 'desc'],
    limit: 50
);
```

## Complete Examples

### Example 1: ActivityService with Pagination

```php
<?php

namespace App\Services;

use App\HasCrossTenantsQuery;
use App\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityService
{
    use HasCrossTenantsQuery;

    protected function getCrossTenantsModelClass(): string
    {
        return Activity::class;
    }

    protected function getCrossTenantsColumns(): array
    {
        return ['id', 'title', 'description', 'status', 'created_at', 'updated_at'];
    }

    /**
     * Get paginated active activities from all tenants
     */
    public function getActiveActivitiesPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getAllFromAllTenantsPaginated(
            perPage: $perPage,
            filters: ['status' => 'active'],
            sorts: ['created_at' => 'desc']
        );
    }

    /**
     * Get all active activities (without pagination)
     */
    public function getAllActiveActivities(int $limit = 100): array
    {
        return $this->getAllFromAllTenants(
            filters: ['status' => 'active'],
            sorts: ['created_at' => 'desc'],
            limit: $limit
        );
    }
}
```

### Example 2: PackageService with Pagination

```php
<?php

namespace App\Services;

use App\HasCrossTenantsQuery;
use App\Models\Package;

class PackageService
{
    use HasCrossTenantsQuery;

    protected function getCrossTenantsModelClass(): string
    {
        return Package::class;
    }

    protected function getCrossTenantsColumns(): array
    {
        return ['id', 'name', 'type', 'price', 'created_at'];
    }

    public function getPackagesPaginated(int $perPage = 20, ?string $type = null)
    {
        $filters = $type ? ['type' => $type] : [];

        return $this->getAllFromAllTenantsPaginated(
            perPage: $perPage,
            filters: $filters,
            sorts: ['price' => 'asc']
        );
    }
}
```

### Example 3: TicketService with Pagination

```php
<?php

namespace App\Services;

use App\HasCrossTenantsQuery;
use App\Models\Ticket;

class TicketService
{
    use HasCrossTenantsQuery;

    protected function getCrossTenantsModelClass(): string
    {
        return Ticket::class;
    }

    protected function getCrossTenantsColumns(): array
    {
        return ['id', 'subject', 'status', 'priority', 'created_at', 'updated_at'];
    }

    public function getOpenTicketsPaginated(int $perPage = 30)
    {
        return $this->getAllFromAllTenantsPaginated(
            perPage: $perPage,
            filters: ['status' => ['open', 'in_progress']],
            sorts: ['priority' => 'desc', 'created_at' => 'asc']
        );
    }
}
```

### Example 4: Controller Usage

```php
<?php

namespace App\Http\Controllers;

use App\Services\UnitService;
use Illuminate\Http\Request;

class CrossTenantUnitController extends Controller
{
    public function __construct(
        private UnitService $unitService
    ) {}

    /**
     * Get paginated units from all tenants
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');

        $filters = $status ? ['status' => $status] : [];

        $units = $this->unitService->getAllFromAllTenantsPaginated(
            perPage: $perPage,
            filters: $filters,
            sorts: ['created_at' => 'desc']
        );

        // Returns JSON with pagination meta
        return response()->json($units);

        // Response format:
        // {
        //   "current_page": 1,
        //   "data": [...],
        //   "first_page_url": "...",
        //   "from": 1,
        //   "last_page": 10,
        //   "last_page_url": "...",
        //   "next_page_url": "...",
        //   "path": "...",
        //   "per_page": 15,
        //   "prev_page_url": null,
        //   "to": 15,
        //   "total": 150
        // }
    }
}
```

## Return Format

### Without Pagination (`getAllFromAllTenants`)

Returns a simple array of records with tenant information:

```php
[
    [
        'id' => 1,
        'name' => 'Example',
        'slug' => 'example',
        'created_at' => '2024-01-01 00:00:00',
        'updated_at' => '2024-01-01 00:00:00',
        'tenant_id' => 'tenant_123',
        'tenant_name' => 'Tenant Company',
        'tenant_domain' => 'tenant.example.com'
    ],
    // ... more records
]
```

### With Pagination (`getAllFromAllTenantsPaginated`)

Returns a Laravel `LengthAwarePaginator` instance with all standard pagination methods:

```php
// Paginator properties
$paginator->total()        // 150 - Total number of records
$paginator->perPage()      // 15 - Items per page
$paginator->currentPage()  // 1 - Current page number
$paginator->lastPage()     // 10 - Last page number
$paginator->hasMorePages() // true - Has more pages?
$paginator->items()        // array - Current page items

// Paginator JSON response
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "Example",
            "tenant_id": "tenant_123",
            "tenant_name": "Tenant Company",
            "tenant_domain": "tenant.example.com"
        }
    ],
    "first_page_url": "http://example.com?page=1",
    "from": 1,
    "last_page": 10,
    "last_page_url": "http://example.com?page=10",
    "next_page_url": "http://example.com?page=2",
    "path": "http://example.com",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 150
}
```

## Performance Notes

### Query Optimization
- **Paginated queries**: Two database round-trips (1 for count, 1 for data)
- **Non-paginated queries**: Single database round-trip
- **UNION ALL strategy**: Combines results from all tenant databases efficiently
- **Database-level operations**: All filtering, sorting, and pagination done at database level (not PHP)

### Count Query Optimization
The count query is optimized to only count records, not fetch them:
```sql
SELECT SUM(cnt) as total FROM (
    SELECT COUNT(*) as cnt FROM tenant1.units WHERE status = 'active'
    UNION ALL
    SELECT COUNT(*) as cnt FROM tenant2.units WHERE status = 'active'
) as counts
```

### Fallback Mechanism
- **Automatic degradation**: If UNION query fails, automatically switches to iterative approach
- **Graceful error handling**: Logs failures but continues with other tenants
- **Consistent API**: Same interface regardless of which strategy is used

### Best Practices
- Use **pagination** for large datasets to avoid memory issues
- Apply **filters** at database level instead of filtering in PHP
- Use **specific columns** (not `*`) to reduce data transfer
- **Cache** tenant list if it doesn't change frequently

## Key Benefits

1. **Laravel-Native Pagination**: Works seamlessly with Laravel's pagination system
2. **Flexible Filtering**: Support for exact matches and IN clauses
3. **Multiple Sorting**: Sort by multiple fields with custom directions
4. **Efficient Counting**: Optimized count queries using SUM of UNIONed counts
5. **OFFSET Support**: Database-level pagination with LIMIT and OFFSET
6. **Error Handling**: Graceful fallback if UNION queries fail
7. **Zero Redundancy**: Shared helper methods (buildUnionQueryParts, buildOrderByClause, etc.)
8. **Extensible**: Easy to extend for custom needs

## API Summary

```php
// Pagination (returns LengthAwarePaginator)
getAllFromAllTenantsPaginated(
    int $perPage = 15,
    array $filters = [],
    array $sorts = [],
    ?int $page = null,
    string $pageName = 'page'
): LengthAwarePaginator

// Simple array (returns array)
getAllFromAllTenants(
    array $filters = [],
    array $sorts = [],
    ?int $limit = null
): array
```
