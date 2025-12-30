<script setup lang="ts">
import reservations from '@/routes/reservations';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { XCircle, KeyRound, Footprints } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { formatCurrency } from '@/helpers/currency';
import { formatSchedule } from '@/helpers/date';

interface Reservation {
    code: string;
    guest_name: string;
    tenant_id: string;
}

interface Props {
    reservation: Reservation;
}

const props = defineProps<Props>();

/**
 * Format time range for display
 * Returns "HH:MM - HH:MM" or "Flexible" if either time is null
 */
function formatTimeRange(startTime: string | null, endTime: string | null): string {
    if (!startTime && !endTime) return 'Flexible';
    if (!startTime) return `Flexible - ${formatTime(endTime)}`;
    if (!endTime) return `${formatTime(startTime)} - Flexible`;
    return `${formatTime(startTime)} - ${formatTime(endTime)}`;
}

/**
 * Format time string (remove seconds if present)
 */
function formatTime(time: string | null): string {
    if (!time) return '';
    // Handle "HH:MM:SS" or "HH:MM" format
    return time.substring(0, 5);
}

const config = {
    resourceName: 'Item',
    resourceNamePlural: 'Items',
    endpoint: reservations.items.url([props.reservation.tenant_id, props.reservation.code]),
    resourceKey: 'items',
    columns: [
        { key: 'product', label: 'Product', sortable: true },
        { key: 'schedule', label: 'Schedule', sortable: true },
        { key: 'duration', label: 'Duration', sortable: true },
        { key: 'rate', label: 'Rate', sortable: true },
        { key: 'quantity', label: 'Qty', sortable: true },
        { key: 'total', label: 'Total', sortable: true },
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
    editRoute: undefined,
};
</script>

<template>
    <BaseIndexPage :title="`Items for ${reservation.code}`" :config="config">
        <!-- Product Column: Name + Type + Tenant -->
        <template #cell-product="{ item }">
            <div class="flex items-start gap-2.5">
                <KeyRound v-if="item.resource_type_label === 'Unit'" class="h-4 w-4 mt-1 text-muted-foreground" />
                <Footprints v-else class="h-4 w-4 mt-1 text-muted-foreground" />
                <div>
                    <div>{{ item.resource_name }} <span class="text-muted-foreground">({{ item.resource_type_label }})</span></div>
                    <div class="text-xs text-muted-foreground">{{ item.tenant_name }}</div>
                </div>
            </div>
        </template>

        <!-- Schedule Column: Dates + Time -->
        <template #cell-schedule="{ item }">
            <div>
                <div>{{ formatSchedule(item.start_date, item.end_date) }}</div>
                <div class="text-xs text-muted-foreground">
                    {{ formatTimeRange(item.start_time, item.end_time) }}
                </div>
            </div>
        </template>

        <!-- Duration Column -->
        <template #cell-duration="{ item }">
            <span>{{ item.duration_label }}</span>
        </template>

        <!-- Rate Column: Name + Price/Type -->
        <template #cell-rate="{ item }">
            <div>
                <div>{{ item.rate_name || '-' }}</div>
                <div class="text-xs text-muted-foreground">
                    {{ formatCurrency(item.rate_price, item.currency) }}<span v-if="item.price_type">/{{ item.price_type }}</span>
                </div>
            </div>
        </template>

        <!-- Quantity Column -->
        <template #cell-quantity="{ item }">
            <div class="ps-2.5">{{ item.quantity }}</div>
        </template>

        <!-- Total Column -->
        <template #cell-total="{ item }">
            <div>{{ formatCurrency(item.line_total, item.currency) }}</div>
        </template>

        <!-- Status Column -->
        <template #cell-status="{ item }">
            <Badge 
                :variant="item.status === 'ACTIVE' ? 'outline' : 'destructive'"
                :class="item.status === 'CANCELLED' ? 'bg-destructive/10 text-destructive' : ''"
            >
                {{ item.status === 'ACTIVE' ? 'Active' : 'Cancelled' }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
