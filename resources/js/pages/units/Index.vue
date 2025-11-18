<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import units from '@/routes/units';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
// import { useFetcher } from '@/composables/useFetcher';
// import { useSorter } from '@/composables/useSorter';
// import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Units',
        href: units.index.url(),
    },
];

// const { resource, fetchData, refresh, lastParams } = useFetcher({
//     endpoint: units.index.url(),
//     resourceKey: 'units',
//     preserveScroll: true,
//     preserveUrl: false,
// });

// const { sortState, handleSort } = useSorter({
//     fetcher: { fetchData, lastParams },
//     backend: true,
// });

// const columns = [
//     { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
//     { key: 'id', label: 'ID', sortable: true },
//     { key: 'domain', label: 'Domain', sortable: true },
//     { key: 'created_at', label: 'Created At', sortable: true },
//     { key: 'updated_at', label: 'Updated At', sortable: true },
// ];
</script>

<template>
    <Head title="Units" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ResourceTableFilter
                :refresh="false"
                searchPlaceholder="Search units..."
                :searchFields="['name', 'id', 'domains.domain']"
                :showAddButton="true"
                addButtonLabel="Add unit"
                :addButtonRoute="units.create.url()"
                :showSearch="false"
            />
            <!-- <ResourceTable
                :data="resource"
                :columns="columns"
                :sortState="sortState"
                :handleSort="handleSort"
                :refresh="refresh"
                :editRoute="(item) => units.edit.url(item.id)"
                :deleteRoute="(item) => ({ url: units.destroy.url(item.id) })"
                resourceName="unit"
            /> -->
        </div>
    </AppLayout>
</template>
