<script setup lang="ts">
import units from '@/routes/units';
import rates from '@/routes/rates';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { formatNumber, formatCurrencyLabel } from '@/helpers/currency';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
    };
}

const props = defineProps<Props>();

const config = {
    resourceName: 'Rate',
    resourceNamePlural: 'Rates',
    endpoint: units.rates.url([props.unit.tenant_id, props.unit.slug]),
    resourceKey: 'rates',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'price', label: 'Price', sortable: true },
        { key: 'currency', label: 'Currency', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'slug'],
    showTable: true,
    addButtonLabel: 'Add rate',
    addButtonRoute: rates.create.url() + `?resource_type=Unit&resource_id=${props.unit.id}&tenant_id=${props.unit.tenant_id}`,
    breadcrumbs: [
        { title: 'Units', href: units.index.url() },
        { title: props.unit.name, href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
        { title: 'Rates', href: '#' },
    ],
    deleteRoute: (item: any) => ({ 
        url: units.rates.delete.url([props.unit.tenant_id, props.unit.slug, item.id])
    }),
    deleteIcon: Trash2,
    deleteActionLabel: 'Delete rate',
    deleteTitle: 'Delete this rate?',
    deleteDescription: 'This will permanently delete this rate from this unit. This action cannot be undone.',
    deleteConfirmLabel: 'Delete rate',
    editRoute: (item: any) => rates.edit.url([props.unit.tenant_id, item.slug]),
    filters: [
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
    <BaseIndexPage :title="`Rates for ${unit.name}`" :config="config">
        <template #cell-price="{ item }">
            {{ formatNumber(item.price) }}
        </template>

        <template #cell-currency="{ item }">
            {{ formatCurrencyLabel(item.currency) }}
        </template>

        <template #cell-status="{ item }">
            <Badge :variant="item.is_active ? 'outline' : 'secondary'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
