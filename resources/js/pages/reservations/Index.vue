<script setup lang="ts">
import reservations from '@/routes/reservations';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Badge } from '@/components/ui/badge';

const config = {
    resourceName: 'Reservation',
    resourceNamePlural: 'Reservations',
    endpoint: reservations.index.url(),
    resourceKey: 'reservations',
    columns: [
        { key: 'code', label: 'Code', sortable: true, className: 'font-mono font-medium' },
        { key: 'guest_name', label: 'Guest Name', sortable: true },
        { key: 'items_count', label: 'Items', sortable: false },
        { key: 'total_amount', label: 'Total', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
        { key: 'tenant_name', label: 'Tenant', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
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
            <span class="text-muted-foreground">{{ item.items_count || 0 }} items</span>
        </template>
        <template #cell-total_amount="{ item }">
            <span class="font-medium">
                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: item.currency || 'IDR', minimumFractionDigits: 0 }).format(item.total_amount || 0) }}
            </span>
        </template>
        <template #cell-status="{ item }">
            <Badge :variant="statusVariants[item.status] || 'secondary'">
                {{ statusLabels[item.status] || item.status }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
