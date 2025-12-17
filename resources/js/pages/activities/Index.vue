<script setup lang="ts">
import activities from '@/routes/activities';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Users, Tags } from 'lucide-vue-next';

const config = {
    resourceName: 'Activity',
    resourceNamePlural: 'Activities',
    endpoint: activities.index.url(),
    resourceKey: 'activities',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'slug', label: 'Slug', sortable: true },
        { key: 'tenant_name', label: 'Tenant', sortable: true },
        { key: 'users_count', label: 'Users', sortable: true, headClassName: 'w-[80px]', className: 'pe-6' },
        { key: 'rates_count', label: 'Rates', sortable: true, headClassName: 'w-[80px]', className: 'pe-6' },
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
        {
            icon: Tags,
            tooltip: 'Rate plans',
            url: (item: any) => activities.rates.url([item.tenant_id, item.slug]),
            variant: 'outline' as const,
        },
    ],
};
</script>

<template>
    <BaseIndexPage title="Activities" :config="config">
        <template #cell-users_count="{ item }">
            <div class="flex items-center gap-2">
                <Users class="h-4 w-4 text-muted-foreground" />
                {{ item.users_count }}
            </div>
        </template>

        <template #cell-rates_count="{ item }">
            <div class="flex items-center gap-2">
                <Tags class="h-4 w-4 text-muted-foreground" />
                {{ item.rates_count }}
            </div>
        </template>
    </BaseIndexPage>
</template>
