<script setup lang="ts">
import reservations from '@/routes/reservations';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { XCircle } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { formatNumber, formatCurrencyLabel } from '@/helpers/currency';

interface Reservation {
    code: string;
    guest_name: string;
    tenant_id: string;
}

interface Props {
    reservation: Reservation;
}

const props = defineProps<Props>();

const config = {
    resourceName: 'Item',
    resourceNamePlural: 'Items',
    endpoint: reservations.items.url([props.reservation.tenant_id, props.reservation.code]),
    resourceKey: 'items',
    columns: [
        { key: 'resource_name', label: 'Resource', sortable: true, className: 'font-medium' },
        { key: 'rate_name', label: 'Rate', sortable: true },
        { key: 'dates', label: 'Dates', sortable: true },
        { key: 'quantity', label: 'Qty', sortable: true },
        { key: 'line_total', label: 'Total', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
    ],
    searchFields: ['resource_name', 'rate_name'],
    showTable: true,
    addButtonLabel: 'Add item',
    addButtonRoute: reservations.items.create.url([props.reservation.tenant_id, props.reservation.code]),
    breadcrumbs: [
        { title: 'Reservations', href: reservations.index.url() },
        { title: props.reservation.code, href: reservations.edit.url([props.reservation.tenant_id, props.reservation.code]) },
        { title: 'Items', href: '#' },
    ],
    deleteRoute: (item: any) => item.status === 'CANCELLED' ? null : ({
        url: reservations.items.cancel.url([props.reservation.tenant_id, props.reservation.code, item.id])
    }),
    deleteIcon: XCircle,
    deleteActionLabel: 'Cancel item',
    deleteTitle: 'Cancel this item?',
    deleteDescription: 'This will mark the item as cancelled and exclude it from the reservation total. This action cannot be undone.',
    deleteConfirmLabel: 'Cancel item',
    // No edit route - items cannot be edited, only cancelled and re-created
    editRoute: undefined,
    filters: [
        {
            name: 'status',
            label: 'Status',
            placeholder: 'Select status',
            options: [
                { value: 'active', label: 'Active' },
                { value: 'cancelled', label: 'Cancelled' },
            ],
        },
        {
            name: 'resource_type',
            label: 'Resource Type',
            placeholder: 'Select type',
            options: [
                { value: 'Room', label: 'Room' },
                { value: 'Activity', label: 'Activity' },
            ],
        },
    ],
};

// Format date range for display
const formatDateRange = (startDate: string | null, endDate: string | null) => {
    if (!startDate) return '-';
    const start = new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    if (!endDate || startDate === endDate) return start;
    const end = new Date(endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    return `${start} - ${end}`;
};
</script>

<template>
    <BaseIndexPage :title="`Items for ${reservation.code}`" :config="config">
        <template #cell-resource_name="{ item }">
            <div class="flex flex-col">
                <span class="font-medium">{{ item.resource_name }}</span>
                <span class="text-xs text-muted-foreground">{{ item.resource_type_label }}</span>
            </div>
        </template>

        <template #cell-rate_name="{ item }">
            <div class="flex flex-col">
                <span>{{ item.rate_name || '-' }}</span>
                <span v-if="item.pricing_type" class="text-xs text-muted-foreground capitalize">
                    {{ item.pricing_type.replace('_', ' ') }}
                </span>
            </div>
        </template>

        <template #cell-dates="{ item }">
            <div class="flex flex-col">
                <span>{{ formatDateRange(item.start_date, item.end_date) }}</span>
                <span v-if="item.formatted_duration" class="text-xs text-muted-foreground">
                    {{ item.formatted_duration }}
                </span>
            </div>
        </template>

        <template #cell-line_total="{ item }">
            <div class="flex flex-col">
                <span class="font-medium">{{ formatNumber(item.line_total) }}</span>
                <span class="text-xs text-muted-foreground">{{ formatCurrencyLabel(item.currency) }}</span>
            </div>
        </template>

        <template #cell-status="{ item }">
            <Badge :variant="item.status === 'ACTIVE' ? 'outline' : 'destructive'">
                {{ item.status === 'ACTIVE' ? 'Active' : 'Cancelled' }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
