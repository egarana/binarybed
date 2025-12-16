<script setup lang="ts">
import rates from '@/routes/rates';
import BaseIndexPage from '@/components/BaseIndexPage.vue';

const config = {
    resourceName: 'Rate',
    resourceNamePlural: 'Rates',
    endpoint: rates.index.url(),
    resourceKey: 'rates',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'price', label: 'Price', sortable: true },
        { key: 'currency', label: 'Currency', sortable: true },
        { key: 'tenant_name', label: 'Tenant', sortable: true },
        { key: 'is_active', label: 'Active', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'slug', 'tenant_name'],
    showTable: true,
    breadcrumbs: [
        { title: 'Rates', href: rates.index.url() },
    ],
    addButtonRoute: rates.create.url(),
    editRoute: (item: any) => rates.edit.url([item.tenant_id, item.slug]),
    deleteRoute: (item: any) => ({ url: rates.destroy.url([item.tenant_id, item.slug]) }),
    itemKey: (item: any) => `${item.tenant_id}-${item.id}`,
};
</script>

<template>
    <BaseIndexPage title="Rates" :config="config" />
</template>
