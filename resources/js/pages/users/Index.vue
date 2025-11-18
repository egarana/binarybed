<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import users from '@/routes/users';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: users.index.url(),
    },
];

const { resource, fetchData, refresh, lastParams } = useFetcher({
    endpoint: users.index.url(),
    resourceKey: 'users',
    preserveScroll: true,
    preserveUrl: false,
});

const { sortState, handleSort } = useSorter({
    fetcher: { fetchData, lastParams },
    backend: true,
});

const columns = [
    { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
    { key: 'updated_at', label: 'Updated At', sortable: true },
];
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ResourceTableFilter
                :refresh="refresh"
                searchPlaceholder="Search users..."
                :searchFields="['name', 'email']"
                :showAddButton="true"
                addButtonLabel="Add user"
                :addButtonRoute="users.create.url()"
                :showSearch="true"
            />
            <ResourceTable
                :data="resource"
                :columns="columns"
                :sortState="sortState"
                :handleSort="handleSort"
                :refresh="refresh"
                :editRoute="(item) => users.edit.url(item.id)"
                :deleteRoute="(item) => ({ url: users.destroy.url(item.id) })"
                resourceName="user"
            />
        </div>
    </AppLayout>
</template>
