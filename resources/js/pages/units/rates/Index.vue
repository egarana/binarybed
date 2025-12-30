<script setup lang="ts">
import units from '@/routes/units';
import rates from '@/routes/rates';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { formatCurrency } from '@/helpers/currency';
import currenciesData from '@/data/currencies.json';

// Build currency filter options from ISO 4217 data
const currencyFilterOptions = currenciesData.map(c => ({
    value: c.code,
    label: `${c.code} (${c.symbol})`
}));

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
        { key: 'status', label: 'Status', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'slug'],
    defaultSort: 'price',
    showTable: true,
    addButtonLabel: 'Add rate',
    addButtonRoute: units.rates.create.url([props.unit.tenant_id, props.unit.slug]),
    breadcrumbs: [
        { title: 'Units', href: units.index.url() },
        { title: props.unit.name, href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
        { title: 'Rates', href: '#' },
    ],
    deleteRoute: (item: any) => item.is_default ? null : ({ 
        url: units.rates.delete.url([props.unit.tenant_id, props.unit.slug, item.id])
    }),
    deleteIcon: Trash2,
    deleteActionLabel: 'Delete rate',
    deleteTitle: 'Delete this rate?',
    deleteDescription: 'This will permanently delete this rate from this unit. This action cannot be undone.',
    deleteConfirmLabel: 'Delete rate',
    editRoute: (item: any) => rates.edit.url([props.unit.tenant_id, props.unit.slug, item.slug]) + '?return=1',
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
            options: currencyFilterOptions,
        },
    ],
};
</script>

<template>
    <BaseIndexPage :title="`Rates for ${unit.name}`" :config="config">
        <template #cell-price="{ item }">
            {{ formatCurrency(item.price, item.currency) }}<span v-if="item.price_type && item.price_type !== 'flat'" class="text-muted-foreground">/<span class="text-xs">{{ item.price_type }}</span></span>
        </template>

        <template #cell-status="{ item }">
            <Badge :variant="item.is_active ? 'outline' : 'secondary'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
