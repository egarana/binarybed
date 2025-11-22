<script setup lang="ts">
import units from '@/routes/units';
import BaseIndexPage from '@/components/BaseIndexPage.vue';

const config = {
    resourceName: 'Unit',
    resourceNamePlural: 'Units',
    endpoint: units.index.url(),
    resourceKey: 'units',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'slug', label: 'Slug', sortable: true },
        { key: 'tenant_name', label: 'Tenant', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'slug', 'tenant_name'],
    addButtonRoute: units.create.url(),
    editRoute: (item: any) => units.edit.url([item.tenant_id, item.slug]),
    deleteRoute: (item: any) => ({ url: units.destroy.url([item.tenant_id, item.slug]) }),
    itemKey: (item: any) => `${item.tenant_id}-${item.id}`,
};
</script>

<template>
    <BaseIndexPage title="Units" :config="config" />
</template>
