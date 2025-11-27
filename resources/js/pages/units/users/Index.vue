<script setup lang="ts">
import units from '@/routes/units';
import BaseIndexPage from '@/components/BaseIndexPage.vue';

const config = {
    resourceName: 'User',
    resourceNamePlural: 'Users',
    endpoint: units.index.url(),
    resourceKey: 'users',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
    ],
    searchFields: ['name', 'email'],
    addButtonLabel: 'Assign User',
};
</script>

<template>
    <BaseIndexPage title="Manage Users" :config="config" />
</template>
