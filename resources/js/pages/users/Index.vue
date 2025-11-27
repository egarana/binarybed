<script setup lang="ts">
import users from '@/routes/users';
import BaseIndexPage from '@/components/BaseIndexPage.vue';

const config = {
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
    showTable: true,
    breadcrumbs: [
        { title: 'Users', href: users.index.url() },
    ],
    addButtonRoute: users.create.url(),
    editRoute: (item: any) => users.edit.url(item.id),
    deleteRoute: (item: any) => ({ url: users.destroy.url(item.id) }),
};
</script>

<template>
    <BaseIndexPage title="Users" :config="config" />
</template>
