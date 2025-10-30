<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import { onMounted } from 'vue';
import ResourceTable from '@/components/ResourceTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index(),
    },
];

const { resource, fetchData, refresh } = useFetcher({
    endpoint: tenants.index(),
    resourceKey: 'tenants',
    preserveScroll: false,
    preserveUrl: false,
});

const { sortState, handleSort } = useSorter({
    fetcher: { fetchData },
    backend: true,
    defaultField: 'name',
});

const columns = [
    { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
    { key: 'id', label: 'ID', sortable: true },
    { key: 'domain', label: 'Domain', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
    { key: 'updated_at', label: 'Updated At', sortable: true },
];
</script>

<template>
    <Head title="Tenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
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
