<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter, { type FilterConfig } from '@/components/ResourceTableFilter.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index(),
    },
];

const { resource, fetchData, refresh, lastParams } = useFetcher({
    endpoint: tenants.index(),
    resourceKey: 'tenants',
    preserveScroll: true,
    preserveUrl: false,
});

const { sortState, handleSort } = useSorter({
    fetcher: { fetchData, lastParams },
    backend: true,
});

const columns = [
    { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
    { key: 'id', label: 'ID', sortable: true },
    { key: 'domain', label: 'Domain', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
    { key: 'updated_at', label: 'Updated At', sortable: true },
];

// Example filters (customize based on your needs)
const filters: FilterConfig[] = [
    {
        name: 'status',
        label: 'Status',
        placeholder: 'Select status',
        options: ['active', 'inactive', 'pending', 'suspended']
    },
    {
        name: 'plan',
        label: 'Plan',
        placeholder: 'Select plan',
        options: [
            { value: 'free', label: 'Free' },
            { value: 'basic', label: 'Basic' },
            { value: 'premium', label: 'Premium' },
            { value: 'enterprise', label: 'Enterprise' }
        ]
    }
];
</script>

<template>
    <Head title="Tenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ResourceTableFilter
                :refresh="refresh"
                searchPlaceholder="Search tenants..."
                :searchFields="['name', 'id', 'domains.domain']"
                :filters="filters"
                :showAddButton="true"
                addButtonLabel="Add Tenant"
                :addButtonRoute="tenants.create()"
                :showSearch="true"
            />
            <ResourceTable
                :data="resource"
                :columns="columns"
                :sortState="sortState"
                :handleSort="handleSort"
                :refresh="refresh"
                :editRoute="(id) => tenants.edit(id)"
                :deleteRoute="(id) => tenants.destroy(id)"
                resourceName="tenant"
            />
        </div>
    </AppLayout>
</template>
