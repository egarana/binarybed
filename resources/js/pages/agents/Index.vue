<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import agents from '@/routes/agents';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agents',
        href: agents.index.url(),
    },
];

const { resource, fetchData, refresh, lastParams } = useFetcher({
    endpoint: agents.index.url(),
    resourceKey: 'agents',
    preserveScroll: true,
    preserveUrl: false,
});

const { sortState, handleSort } = useSorter({
    fetcher: { fetchData, lastParams },
    backend: true,
});

const columns = [
    { key: 'code', label: 'Code', sortable: true, className: 'font-medium' },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
    { key: 'updated_at', label: 'Updated At', sortable: true },
];
</script>

<template>
    <Head title="Agents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ResourceTableFilter
                :refresh="refresh"
                searchPlaceholder="Search agents..."
                :searchFields="['code', 'name', 'email']"
                :showAddButton="true"
                addButtonLabel="Add agent"
                :addButtonRoute="agents.create.url()"
                :showSearch="true"
            />
            <ResourceTable
                :data="resource"
                :columns="columns"
                :sortState="sortState"
                :handleSort="handleSort"
                :refresh="refresh"
                :editRoute="(item) => agents.edit.url(item.id)"
                :deleteRoute="(item) => ({ url: agents.destroy.url(item.id) })"
                resourceName="agent"
            />
        </div>
    </AppLayout>
</template>
