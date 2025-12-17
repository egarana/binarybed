<script setup lang="ts">
import rates from '@/routes/rates';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { KeyRound, Footprints } from 'lucide-vue-next';
import { formatNumber, formatCurrencyLabel } from '@/helpers/currency';
import { Badge } from '@/components/ui/badge';

const config = {
    resourceName: 'Rate',
    resourceNamePlural: 'Rates',
    endpoint: rates.index.url(),
    resourceKey: 'rates',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'price', label: 'Price', sortable: true },
        { key: 'currency', label: 'Currency', sortable: true },
        { key: 'product', label: 'Product', sortable: true },
        { key: 'type', label: 'Type', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'slug', 'tenant_name', 'resource_name'],
    showTable: true,
    breadcrumbs: [
        { title: 'Rates', href: rates.index.url() },
    ],
    addButtonRoute: rates.create.url(),
    editRoute: (item: any) => rates.edit.url([item.tenant_id, item.resource_slug, item.slug]),
    deleteRoute: (item: any) => ({ url: rates.destroy.url([item.tenant_id, item.resource_slug, item.slug]) }),
    itemKey: (item: any) => `${item.tenant_id}-${item.id}`,
    filters: [
        {
            name: 'type',
            label: 'Type',
            placeholder: 'Select type',
            options: [
                { value: 'unit', label: 'Unit' },
                { value: 'activity', label: 'Activity' },
            ],
        },
        {
            name: 'status',
            label: 'Status',
            placeholder: 'Select status',
            options: [
                { value: 'active', label: 'Active' },
                { value: 'inactive', label: 'Inactive' },
            ],
        },
        {
            name: 'currency',
            label: 'Currency',
            placeholder: 'Select currency',
            options: [
                { value: 'IDR', label: 'IDR (Rp)' },
                { value: 'USD', label: 'USD ($)' },
                { value: 'EUR', label: 'EUR (€)' },
                { value: 'JPY', label: 'JPY (¥)' },
                { value: 'SGD', label: 'SGD (S$)' },
                { value: 'AUD', label: 'AUD (A$)' },
                { value: 'GBP', label: 'GBP (£)' },
            ],
        },
    ],
};
</script>

<template>
    <BaseIndexPage title="Rates" :config="config">
        <template #cell-price="{ item }">
            {{ formatNumber(item.price) }}
        </template>

        <template #cell-currency="{ item }">
            {{ formatCurrencyLabel(item.currency) }}
        </template>

        <template #cell-product="{ item }">
            <div>{{ item.resource_name }}</div>
            <div class="text-xs text-muted-foreground">{{ item.tenant_name }}</div>
        </template>

        <template #cell-type="{ item }">
            <div class="flex items-center gap-2">
                <KeyRound v-if="item.rateable_type === 'Unit'" class="h-4 w-4 text-muted-foreground" />
                <Footprints v-else class="h-4 w-4 text-muted-foreground" />
                {{ item.rateable_type }}
            </div>
        </template>

        <template #cell-status="{ item }">
            <Badge 
                :variant="item.is_active ? 'outline' : 'secondary'"
            >
                {{ item.is_active ? 'Active' : 'Inactive' }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>

