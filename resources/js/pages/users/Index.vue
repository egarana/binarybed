<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import users from '@/routes/users';
import { Head } from '@inertiajs/vue3';
import { useResourceIndex } from '@/composables/useResourceIndex';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

const { breadcrumbs, resource, refresh, sortState, handleSort, filterConfig, tableConfig } =
    useResourceIndex({
        resourceName: 'User',
        resourceNamePlural: 'Users',
        endpoint: users.index.url(),
        resourceKey: 'users',
        columns: [
            { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
            { key: 'email', label: 'Email', sortable: true },
            { key: 'created_at', label: 'Created At', sortable: true },
            { key: 'updated_at', label: 'Updated At', sortable: true },
        ],
        searchFields: ['name', 'email'],
        addButtonRoute: users.create.url(),
        editRoute: (item) => users.edit.url(item.id),
        deleteRoute: (item) => ({ url: users.destroy.url(item.id) }),
    });
</script>

<template>
    <Head title="Users" />

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
