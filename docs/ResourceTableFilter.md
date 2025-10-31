# ResourceTableFilter Component

Component untuk search/filter dan add button yang digunakan bersama ResourceTable.

## Features

- ✅ Search input dengan debounce (via useFetcher)
- ✅ Reset button
- ✅ Add button (optional)
- ✅ Opsi disable/enable
- ✅ Customizable placeholder dan labels
- ✅ Support Spatie QueryBuilder filters

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `searchPlaceholder` | `string` | `"Search..."` | Placeholder untuk search input |
| `showSearch` | `boolean` | `true` | Show/hide search input |
| `showAddButton` | `boolean` | `false` | Show/hide add button |
| `addButtonLabel` | `string` | `"Add"` | Label untuk add button |
| `addButtonRoute` | `string` | - | Route untuk add button (required jika showAddButton=true) |
| `disabled` | `boolean` | `false` | Disable semua input/button |
| `refresh` | `function` | - | Function dari useFetcher untuk trigger refresh |
| `searchField` | `string` | `"name"` | Field name untuk search (e.g., 'name', 'email') |
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

### 3. Search by Different Field

```vue
<!-- Search by email instead of name -->
<ResourceTableFilter
    :refresh="refresh"
    searchPlaceholder="Search by email..."
    searchField="email"
/>
```

### 4. Disabled State

```vue
<ResourceTableFilter
    :refresh="refresh"
    :disabled="true"
    searchPlaceholder="Search..."
/>
```

### 5. Hide Search, Only Show Add Button

```vue
<ResourceTableFilter
    :refresh="refresh"
    :showSearch="false"
    :showAddButton="true"
    addButtonLabel="Create New"
    :addButtonRoute="'/create'"
/>
```

### 6. Complete Example with ResourceTable

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
            searchField="name"
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

### Example Repository:

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

## How It Works

1. User mengetik di search input
2. Watch trigger dengan debounce (300ms default dari useFetcher)
3. Call `refresh()` dengan parameter `filter[{searchField}]={value}`
4. Backend Spatie QueryBuilder akan filter data sesuai field
5. Table auto-update dengan data yang di-filter

## Notes

- Search menggunakan Spatie QueryBuilder filter format: `filter[field]=value`
- Debounce sudah di-handle oleh `useFetcher` (default 300ms)
- Reset button akan clear search dan refresh dengan filter kosong
- Search field hanya support 1 field, jika butuh multiple field search, perlu custom filter di backend
