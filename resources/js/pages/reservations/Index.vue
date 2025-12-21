<script setup lang="ts">
import reservations from '@/routes/reservations';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Badge } from '@/components/ui/badge';
import { Package, KeyRound, Footprints } from 'lucide-vue-next';
import { formatNumber, formatCurrencyLabel } from '@/helpers/currency';

const config = {
    resourceName: 'Reservation',
    resourceNamePlural: 'Reservations',
    endpoint: reservations.index.url(),
    resourceKey: 'reservations',
    columns: [
        { key: 'code', label: 'Code', sortable: true, className: 'font-mono font-light' },
        { key: 'guest_name', label: 'Guest Name', sortable: true, className: 'font-medium' },
        { key: 'guest_email', label: 'Guest Email', sortable: true },
        { key: 'items_count', label: 'Items', sortable: true },
        { key: 'total_amount', label: 'Total', sortable: true },
        { key: 'currency', label: 'Currency', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
        { key: 'tenant_name', label: 'Tenant', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['code', 'guest_name', 'guest_email', 'tenant_name'],
    showTable: true,
    breadcrumbs: [
        { title: 'Reservations', href: reservations.index.url() },
    ],
    addButtonRoute: reservations.create.url(),
    editRoute: (item: any) => reservations.edit.url([item.tenant_id, item.code]),
    // No deleteRoute - reservations cannot be deleted
    itemKey: (item: any) => `${item.tenant_id}-${item.id}`,
    customActions: [
        {
            icon: Package,
            tooltip: 'Manage items',
            url: (item: any) => reservations.items.url([item.tenant_id, item.code]),
            variant: 'outline' as const,
        },
    ],
    filters: [
        {
            name: 'status',
            label: 'Status',
            placeholder: 'Select status',
            options: [
                { value: 'PENDING', label: 'Pending' },
                { value: 'CONFIRMED', label: 'Confirmed' },
                { value: 'CANCELLED', label: 'Cancelled' },
                { value: 'COMPLETED', label: 'Completed' },
                { value: 'NO_SHOW', label: 'No Show' },
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
        {
            name: 'items',
            label: 'Items',
            placeholder: 'Select item types',
            options: [
                { value: 'Room', label: 'Unit' },
                { value: 'Activity', label: 'Activity' },
            ],
        },
    ],
};

// Status badge variants
const statusVariants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    PENDING: 'secondary',
    CONFIRMED: 'default',
    CANCELLED: 'destructive',
    COMPLETED: 'outline',
    NO_SHOW: 'destructive',
};

const statusLabels: Record<string, string> = {
    PENDING: 'Pending',
    CONFIRMED: 'Confirmed',
    CANCELLED: 'Cancelled',
    COMPLETED: 'Completed',
    NO_SHOW: 'No Show',
};
</script>

<template>
    <BaseIndexPage title="Reservations" :config="config">
        <template #cell-items_count="{ item }">
            <div class="flex gap-4">
                <div class="flex items-center gap-2">
                    <KeyRound class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm">{{ item.items_by_type?.Room || 0 }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Footprints class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm">{{ item.items_by_type?.Activity || 0 }}</span>
                </div>
            </div>
        </template>
        <template #cell-total_amount="{ item }">
            {{ formatNumber(item.total_amount || 0) }}
        </template>

        <template #cell-currency="{ item }">
            {{ formatCurrencyLabel(item.currency) }}
        </template>

        <template #cell-status="{ item }">
            <Badge 
                :variant="statusVariants[item.status] || 'secondary'"
                :class="item.status === 'CANCELLED' ? 'bg-destructive/10 text-destructive' : ''"
            >
                {{ statusLabels[item.status] || item.status }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
