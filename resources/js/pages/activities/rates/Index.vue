<script setup lang="ts">
import activities from '@/routes/activities';
import rates from '@/routes/rates';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Trash2, DollarSign } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { formatCurrency } from '@/helpers/currency';

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
        { key: 'currency', label: 'Currency', sortable: true },
        { key: 'is_active', label: 'Status', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
    ],
    searchFields: ['name', 'slug'],
    showTable: true,
    addButtonLabel: 'Add rate',
    addButtonRoute: rates.create.url() + `?resource_type=Activity&resource_id=${props.activity.id}&tenant_id=${props.activity.tenant_id}`,
    breadcrumbs: [
        { title: 'Activities', href: activities.index.url() },
        { title: props.activity.name, href: activities.edit.url([props.activity.tenant_id, props.activity.slug]) },
        { title: 'Rates', href: '#' },
    ],
    deleteRoute: (item: any) => ({ 
        url: activities.rates.delete.url([props.activity.tenant_id, props.activity.slug, item.id])
    }),
    deleteIcon: Trash2,
    deleteActionLabel: 'Delete rate',
    deleteTitle: 'Delete this rate?',
    deleteDescription: 'This will permanently delete this rate from this activity. This action cannot be undone.',
    deleteConfirmLabel: 'Delete rate',
    editRoute: (item: any) => rates.edit.url([props.activity.tenant_id, item.slug]),
};


</script>

<template>
    <BaseIndexPage :title="`Rates for ${activity.name}`" :config="config">
        <template #cell-price="{ item }">
            <div class="flex items-center gap-1">
                <DollarSign class="h-4 w-4 text-muted-foreground" />
                {{ formatCurrency(item.price, item.currency) }}
            </div>
        </template>

        <template #cell-is_active="{ item }">
            <Badge :variant="item.is_active ? 'default' : 'secondary'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
