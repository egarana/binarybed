<script setup lang="ts">
import tenants from '@/routes/tenants';
import BaseIndexPage from '@/components/BaseIndexPage.vue';

const config = {
    resourceName: 'Tenant',
    resourceNamePlural: 'Tenants',
    endpoint: tenants.index.url(),
    resourceKey: 'tenants',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'id', label: 'ID', sortable: true },
        { key: 'domain', label: 'Domain', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'id', 'domains.domain'],
    showTable: true,
    breadcrumbs: [
        { title: 'Tenants', href: tenants.index.url() },
    ],
    addButtonRoute: tenants.create.url(),
    editRoute: (item: any) => tenants.edit.url(item.id),
    deleteRoute: (item: any) => ({ url: tenants.destroy.url(item.id) }),
    useDomainHttps: false,
};
</script>

<template>
    <BaseIndexPage title="Tenants" :config="config">
        <template #cell-domain="{ value }">
            <a 
                :href="`${config.useDomainHttps ? 'https' : 'http'}://${value}`" 
                target="_blank" 
                rel="noopener noreferrer"
                class="text-primary hover:underline"
            >
                {{ value }}
            </a>
        </template>
    </BaseIndexPage>
</template>
