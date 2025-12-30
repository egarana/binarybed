<script setup lang="ts">
import activities from '@/routes/activities';
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
    activity: {
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
    endpoint: activities.rates.url([props.activity.tenant_id, props.activity.slug]),
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
    addButtonRoute: activities.rates.create.url([props.activity.tenant_id, props.activity.slug]),
    breadcrumbs: [
        { title: 'Activities', href: activities.index.url() },
        { title: props.activity.name, href: activities.edit.url([props.activity.tenant_id, props.activity.slug]) },
        { title: 'Rates', href: '#' },
    ],
    deleteRoute: (item: any) => item.is_default ? null : ({ 
        url: activities.rates.delete.url([props.activity.tenant_id, props.activity.slug, item.id])
    }),
    deleteIcon: Trash2,
    deleteActionLabel: 'Delete rate',
    deleteTitle: 'Delete this rate?',
    deleteDescription: 'This will permanently delete this rate from this activity. This action cannot be undone.',
    deleteConfirmLabel: 'Delete rate',
    editRoute: (item: any) => rates.edit.url([props.activity.tenant_id, props.activity.slug, item.slug]) + '?return=1',
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
    <BaseIndexPage :title="`Rates for ${activity.name}`" :config="config">
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
