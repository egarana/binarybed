<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import units from '@/routes/units';
import { Head } from '@inertiajs/vue3';
import { useResourceIndex } from '@/composables/useResourceIndex';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

const { breadcrumbs, resource, refresh, sortState, handleSort, filterConfig, tableConfig } =
    useResourceIndex({
        resourceName: 'Unit',
        resourceNamePlural: 'Units',
        endpoint: units.index.url(),
        resourceKey: 'units',
        columns: [
            { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
            { key: 'slug', label: 'Slug', sortable: true },
            { key: 'tenant_name', label: 'Tenant', sortable: true },
            { key: 'created_at', label: 'Created At', sortable: true },
            { key: 'updated_at', label: 'Updated At', sortable: true },
        ],
        searchFields: ['name', 'slug', 'tenant_name'],
        addButtonRoute: units.create.url(),
        editRoute: (item) => units.edit.url([item.tenant_id, item.slug]),
        deleteRoute: (item) => ({ url: units.destroy.url([item.tenant_id, item.slug]) }),
        itemKey: (item) => `${item.tenant_id}-${item.id}`,
    });
</script>

<template>
    <Head title="Units" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ResourceTableFilter :refresh="refresh" v-bind="filterConfig" />
            <ResourceTable
                :data="resource"
                :sortState="sortState"
                :handleSort="handleSort"
                :refresh="refresh"
                v-bind="tableConfig"
            />
        </div>
    </AppLayout>
</template>
