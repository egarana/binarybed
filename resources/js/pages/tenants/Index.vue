<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { Head } from '@inertiajs/vue3';
import { useResourceIndex } from '@/composables/useResourceIndex';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

const { breadcrumbs, resource, refresh, sortState, handleSort, filterConfig, tableConfig } =
    useResourceIndex({
        resourceName: 'Tenant',
        resourceNamePlural: 'Tenants',
        endpoint: tenants.index.url(),
        resourceKey: 'tenants',
        columns: [
            { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
            { key: 'id', label: 'ID', sortable: true },
            { key: 'domain', label: 'Domain', sortable: true },
            { key: 'created_at', label: 'Created At', sortable: true },
            { key: 'updated_at', label: 'Updated At', sortable: true },
        ],
        searchFields: ['name', 'id', 'domains.domain'],
        addButtonRoute: tenants.create.url(),
        editRoute: (item) => tenants.edit.url(item.id),
        deleteRoute: (item) => ({ url: tenants.destroy.url(item.id) }),
    });
</script>

<template>
    <Head title="Tenants" />

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
