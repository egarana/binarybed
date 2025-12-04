<script setup lang="ts">
import activities from '@/routes/activities';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Users } from 'lucide-vue-next';

const config = {
    resourceName: 'Activity',
    resourceNamePlural: 'Activities',
    endpoint: activities.index.url(),
    resourceKey: 'activities',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'slug', label: 'Slug', sortable: true },
        { key: 'tenant_name', label: 'Tenant', sortable: true },
        { key: 'users_count', label: 'Users', sortable: true, headClassName: 'w-[50px]', className: 'text-center pe-6' },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'slug', 'tenant_name'],
    showTable: true,
    breadcrumbs: [
        { title: 'Activities', href: activities.index.url() },
    ],
    addButtonRoute: activities.create.url(),
    editRoute: (item: any) => activities.edit.url([item.tenant_id, item.slug]),
    deleteRoute: (item: any) => ({ url: activities.destroy.url([item.tenant_id, item.slug]) }),
    itemKey: (item: any) => `${item.tenant_id}-${item.id}`,
    customActions: [
        {
            icon: Users,
            tooltip: 'Manage users',
            url: (item: any) => activities.users.url([item.tenant_id, item.slug]),
            variant: 'outline' as const,
        },
    ],
};
</script>

<template>
    <BaseIndexPage title="Activities" :config="config" />
</template>
