# HasCrossTenantsQuery Trait - Usage Guide

The `HasCrossTenantsQuery` trait allows any model to query data across all tenants efficiently using optimized UNION queries.

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

Use the `getAllFromAllTenants()` method:

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

### Example 1: ActivityService

```php
<?php

namespace App\Services;

use App\HasCrossTenantsQuery;
use App\Models\Activity;

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

    public function getAllActiveActivitiesAcrossTenants()
    {
        return $this->getAllFromAllTenants(
            filters: ['status' => 'active'],
            sorts: ['created_at' => 'desc'],
            limit: 100
        );
    }
}
```

### Example 2: PackageService

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

    public function getAllPackagesAcrossTenants()
    {
        return $this->getAllFromAllTenants(
            sorts: ['price' => 'asc']
        );
    }
}
```

### Example 3: TicketService

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

    public function getAllOpenTicketsAcrossTenants()
    {
        return $this->getAllFromAllTenants(
            filters: ['status' => ['open', 'in_progress']],
            sorts: ['priority' => 'desc', 'created_at' => 'asc']
        );
    }
}
```

## Return Format

All queries return an array of records with tenant information included:

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

## Performance Notes

- **Optimized UNION query**: Single database round-trip, best for production use
- **Automatic fallback**: If UNION query fails, automatically falls back to iterative approach
- **Database-level filtering & sorting**: More efficient than PHP-level processing
- **Tenant metadata**: Automatically includes tenant_id, tenant_name, and tenant_domain

## Features

1. **Flexible filtering**: Support for exact matches and IN clauses
2. **Multiple sorting**: Sort by multiple fields with custom directions
3. **Limit support**: Efficient limiting at database level
4. **Error handling**: Graceful fallback if queries fail
5. **Extensible**: Easy to extend for custom needs
