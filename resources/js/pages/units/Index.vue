<script setup lang="ts">
import units from '@/routes/units';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Users, Tags } from 'lucide-vue-next';

const config = {
    resourceName: 'Unit',
    resourceNamePlural: 'Units',
    endpoint: units.index.url(),
    resourceKey: 'units',
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
        { title: 'Units', href: units.index.url() },
    ],
    addButtonRoute: units.create.url(),
    editRoute: (item: any) => units.edit.url([item.tenant_id, item.slug]),
    deleteRoute: (item: any) => ({ url: units.destroy.url([item.tenant_id, item.slug]) }),
    itemKey: (item: any) => `${item.tenant_id}-${item.id}`,
    customActions: [
        {
            icon: Users,
            tooltip: 'Manage users',
            url: (item: any) => units.users.url([item.tenant_id, item.slug]),
            variant: 'outline' as const,
        },
        {
            icon: Tags,
            tooltip: 'Rate plans',
            url: (item: any) => units.rates.url([item.tenant_id, item.slug]),
            variant: 'outline' as const,
        },
    ],
};
</script>

<template>
    <BaseIndexPage title="Units" :config="config">
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
