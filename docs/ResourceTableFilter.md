# ResourceTableFilter Component

Component untuk search/filter dan add button yang digunakan bersama ResourceTable.

## Features

- ✅ Search input dengan debounce (via useFetcher)
- ✅ **Multi-field search** - search across multiple fields with OR condition
- ✅ Single field search
- ✅ Reset button
- ✅ Add button (optional)
- ✅ Opsi disable/enable
- ✅ Customizable placeholder dan labels
- ✅ Support Spatie QueryBuilder filters
- ✅ **Clean URLs** - no encoded characters like %5B or %2C

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `searchPlaceholder` | `string` | `"Search..."` | Placeholder untuk search input |
| `showSearch` | `boolean` | `true` | Show/hide search input |
| `showAddButton` | `boolean` | `false` | Show/hide add button |
| `addButtonLabel` | `string` | `"Add"` | Label untuk add button |
| `addButtonRoute` | `InertiaLinkProps['href']` | - | Route untuk add button - accepts string atau route object (required jika showAddButton=true) |
| `disabled` | `boolean` | `false` | Disable semua input/button |
| `refresh` | `function` | - | Function dari useFetcher untuk trigger refresh |
| `searchField` | `string` | - | Single field name untuk search (e.g., 'name', 'email') |
| `searchFields` | `string[]` | - | Multiple fields untuk search dengan OR condition (e.g., ['name', 'email', 'id']) |
| `initialSearch` | `string` | `""` | Initial search value |

## Usage Examples

### 1. Basic Usage (Search Only)

```vue
<script setup lang="ts">
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';
import { useFetcher } from '@/composables/useFetcher';

const { refresh } = useFetcher({
    endpoint: '/api/users',
    resourceKey: 'users',
});
</script>

<template>
    <ResourceTableFilter
        :refresh="refresh"
        searchPlaceholder="Search users..."
        searchField="name"
    />
</template>
```

### 2. With Add Button

```vue
<ResourceTableFilter
    :refresh="refresh"
    searchPlaceholder="Search tenants..."
    searchField="name"
    :showAddButton="true"
    addButtonLabel="Add Tenant"
    :addButtonRoute="tenants.create()"
/>
```

### 3. Multi-Field Search (Search Across Multiple Fields)

```vue
<!-- Search across name, email, and id with OR condition -->
<ResourceTableFilter
    :refresh="refresh"
    searchPlaceholder="Search tenants..."
    :searchFields="['name', 'id', 'domain']"
/>
```

**How it works:**
- Ketik "john" akan search di: `name LIKE '%john%' OR id LIKE '%john%' OR domain LIKE '%john%'`
- Backend menggunakan `MultiFieldSearchFilter` dengan OR condition
- **Clean URL format:** `?search=john&fields=name,id,domain` ✨
- No encoded characters (%5B, %2C, etc.) - readable dan SEO friendly!

### 4. Search by Different Field (Single Field)

```vue
<!-- Search by email instead of name -->
<ResourceTableFilter
    :refresh="refresh"
    searchPlaceholder="Search by email..."
    searchField="email"
/>
```

### 5. Disabled State

```vue
<ResourceTableFilter
    :refresh="refresh"
    :disabled="true"
    searchPlaceholder="Search..."
/>
```

### 6. Hide Search, Only Show Add Button

```vue
<ResourceTableFilter
    :refresh="refresh"
    :showSearch="false"
    :showAddButton="true"
    addButtonLabel="Create New"
    :addButtonRoute="'/create'"
/>
```

### 7. Complete Example with ResourceTable and Multi-Field Search

```vue
<script setup lang="ts">
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';
import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import routes from '@/routes/users';

const { resource, fetchData, refresh, lastParams } = useFetcher({
    endpoint: routes.index(),
    resourceKey: 'users',
    preserveScroll: true,
    preserveUrl: false,
});

const { sortState, handleSort } = useSorter({
    fetcher: { fetchData, lastParams },
    backend: true,
});

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
];
</script>

<template>
    <div class="flex flex-col gap-4">
        <ResourceTableFilter
            :refresh="refresh"
            searchPlaceholder="Search users..."
            :searchFields="['name', 'email', 'id']"
            :showAddButton="true"
            addButtonLabel="Add User"
            :addButtonRoute="routes.create()"
        />
        <ResourceTable
            :data="resource"
            :columns="columns"
            :sortState="sortState"
            :handleSort="handleSort"
            :refresh="refresh"
            :editRoute="(id) => routes.edit(id)"
            :deleteRoute="(id) => routes.destroy(id)"
            resourceName="user"
        />
    </div>
</template>
```

## Backend Requirements

Backend harus menggunakan **Spatie QueryBuilder** dengan `allowedFilters` yang sesuai.

### Option 1: Single Field Search

Untuk single field search (`searchField`), cukup tambahkan field ke `allowedFilters`:

```php
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

private function baseQuery(): QueryBuilder
{
    return QueryBuilder::for(User::class)
        ->allowedFilters([
            'name',
            'email',
            'id',
            // ... other filters
        ])
        ->allowedSorts([
            'name', 'email', 'created_at', 'updated_at',
        ])
        ->defaultSort('name');
}

public function paginate(Request $request)
{
    $perPage = $this->pagination->resolvePerPage($request);

    return $this->baseQuery()
        ->paginate($perPage)
        ->appends($request->query());
}
```

### Option 2: Multi-Field Search (Recommended)

Untuk multi-field search (`searchFields`), gunakan custom filter `MultiFieldSearchFilter`:

**1. Custom Filter sudah tersedia di:**
`app/QueryBuilder/Filters/MultiFieldSearchFilter.php`

**2. Update Repository:**

```php
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;

private function baseQuery(): QueryBuilder
{
    return QueryBuilder::for(User::class)
        ->allowedFilters([
            'name',
            'email',
            'id',
            // Multi-field search filter
            AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'email', 'id'])),
        ])
        ->allowedSorts([
            'name', 'email', 'created_at', 'updated_at',
        ])
        ->defaultSort('name');
}

public function paginate(Request $request)
{
    $perPage = $this->pagination->resolvePerPage($request);

    // Map clean URL params to Spatie QueryBuilder format
    if ($request->has('search')) {
        $request->merge(['filter' => array_merge(
            $request->input('filter', []),
            ['search' => $request->input('search')]
        )]);
    }

    return $this->baseQuery()
        ->paginate($perPage)
        ->appends($request->query());
}
```

**3. How Clean URLs Work:**

The `paginate()` method above maps clean URL params to Spatie QueryBuilder format internally:
- Frontend sends: `?search=john&fields=name,email`
- Repository maps to: `?filter[search]=john` (internally)
- Spatie QueryBuilder processes the filter
- Result: Clean, readable URLs without %5B encoding! ✨

**4. Multi-Field Search dengan Relasi:**

`MultiFieldSearchFilter` support relation fields dengan format `relation.column`:

```php
AllowedFilter::custom('search', new MultiFieldSearchFilter([
    'name',
    'email',
    'id',
    'profile.phone',      // Search di relasi profile
    'company.name',       // Search di relasi company
]))
```

**Example Query:**
- Clean URL: `?search=john&fields=name,email,id` ✨
- SQL: `WHERE name LIKE '%john%' OR email LIKE '%john%' OR id LIKE '%john%'`

## How It Works

### Single Field Search
1. User mengetik di search input
2. Watch trigger dengan debounce (300ms default dari useFetcher)
3. Call `refresh()` dengan parameter `filter[{searchField}]={value}`
4. Backend Spatie QueryBuilder akan filter data sesuai field
5. Table auto-update dengan data yang di-filter

### Multi-Field Search
1. User mengetik di search input
2. Watch trigger dengan debounce (300ms default dari useFetcher)
3. Call `refresh()` dengan **clean URL parameters**: `search={value}&fields=field1,field2,field3`
4. Repository maps clean params to Spatie QueryBuilder format internally
5. Backend `MultiFieldSearchFilter` builds OR condition untuk semua fields
6. SQL query: `WHERE field1 LIKE '%value%' OR field2 LIKE '%value%' OR field3 LIKE '%value%'`
7. Table auto-update dengan data yang di-filter

## Notes

- **Single field**: Menggunakan format `filter[field]=value` (standard Spatie QueryBuilder)
- **Multi-field**: Menggunakan **clean URL format** `search=value&fields=field1,field2` ✨
- No URL encoding (%5B, %2C) - URLs are clean and readable!
- Debounce sudah di-handle oleh `useFetcher` (default 300ms)
- Reset button akan clear search dan refresh dengan filter kosong
- Multi-field search support relation fields dengan format `relation.column`
- Backend menggunakan LIKE dengan wildcards untuk partial search
- Repository automatically maps clean URLs to Spatie QueryBuilder format