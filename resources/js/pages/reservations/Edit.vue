<script setup lang="ts">
import reservations from '@/routes/reservations';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface ReservationItem {
    id: number;
    resource_name: string;
    resource_type_label: string;
    rate_name: string;
    quantity: number;
    duration_days: number | null;
    duration_minutes: number | null;
    rate_price: number;
    line_total: number;
    currency: string;
    status: string;
    start_date: string | null;
    end_date: string | null;
}

interface Props {
    reservation: {
        id: number;
        code: string;
        guest_name: string;
        guest_email: string | null;
        guest_phone: string | null;
        status: string;
        subtotal: number;
        total_amount: number;
        currency: string;
        notes: string | null;
        cancellation_reason: string | null;
        tenant_id: string;
        items: ReservationItem[];
        created_at: string;
        updated_at: string;
    };
    statuses: string[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Reservations', href: reservations.index.url() },
    { title: `Edit: ${props.reservation.code}`, href: reservations.edit.url([props.reservation.tenant_id, props.reservation.code]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'reservation',
    action: 'update',
});

// Form fields
const guestName = ref(props.reservation.guest_name);
const guestEmail = ref(props.reservation.guest_email || '');
const guestPhone = ref(props.reservation.guest_phone || '');
const status = ref(props.reservation.status);
const notes = ref(props.reservation.notes || '');
const cancellationReason = ref(props.reservation.cancellation_reason || '');

// Status labels for display
const statusLabels: Record<string, string> = {
    PENDING: 'Pending',
    CONFIRMED: 'Confirmed',
    CANCELLED: 'Cancelled',
    COMPLETED: 'Completed',
    NO_SHOW: 'No Show',
};

// Show cancellation reason field when status is CANCELLED
const showCancellationReason = computed(() => status.value === 'CANCELLED');

// Active items
const activeItems = computed(() => props.reservation.items?.filter(item => item.status === 'ACTIVE') || []);

// Format currency
const formatCurrency = (amount: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency, minimumFractionDigits: 0 }).format(amount);
};

// Transform to include all fields in form data
function transformFormData(data: Record<string, any>) {
    return {
        ...data,
        status: status.value,
        notes: notes.value,
        cancellation_reason: cancellationReason.value,
    };
}
</script>

<template>
    <BaseFormPage
        :title="`Edit Reservation: ${reservation.code}`"
        :breadcrumbs="breadcrumbs"
        :action="reservations.update.url([reservation.tenant_id, reservation.code])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="transformFormData"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="Reservation Code"
                :value="reservation.code"
                help-text="Reservation code cannot be changed"
            />

            <FormField
                id="guest_name"
                label="Guest Name"
                type="text"
                :tabindex="1"
                autocomplete="name"
                placeholder="e.g. John Doe"
                v-model="guestName"
                :error="errors.guest_name"
            />

            <FormField
                id="guest_email"
                label="Email"
                type="email"
                :tabindex="2"
                autocomplete="email"
                placeholder="e.g. john@example.com"
                v-model="guestEmail"
                :error="errors.guest_email"
                :required="false"
            />

            <FormField
                id="guest_phone"
                label="Phone"
                type="tel"
                :tabindex="3"
                autocomplete="tel"
                placeholder="e.g. +628123456789"
                v-model="guestPhone"
                :error="errors.guest_phone"
                :required="false"
            />

            <!-- Status Selection -->
            <div class="grid gap-2">
                <Label for="status">Status</Label>
                <Select v-model="status" :disabled="processing">
                    <SelectTrigger id="status" :tabindex="4">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="s in statuses" :key="s" :value="s">
                            {{ statusLabels[s] || s }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="errors.status" class="text-sm text-destructive">{{ errors.status }}</p>
            </div>

            <!-- Cancellation Reason (shown when status is CANCELLED) -->
            <div v-if="showCancellationReason" class="grid gap-2">
                <Label for="cancellation_reason">Cancellation Reason</Label>
                <Textarea
                    id="cancellation_reason"
                    name="cancellation_reason"
                    v-model="cancellationReason"
                    placeholder="Why is this reservation being cancelled?"
                    :tabindex="5"
                    :disabled="processing"
                    rows="2"
                />
                <p v-if="errors.cancellation_reason" class="text-sm text-destructive">{{ errors.cancellation_reason }}</p>
            </div>

            <!-- Notes -->
            <div class="grid gap-2">
                <Label for="notes">Notes <span class="text-muted-foreground">(Optional)</span></Label>
                <Textarea
                    id="notes"
                    name="notes"
                    v-model="notes"
                    placeholder="Add any special requests or notes..."
                    :tabindex="6"
                    :disabled="processing"
                    rows="3"
                />
                <p v-if="errors.notes" class="text-sm text-destructive">{{ errors.notes }}</p>
            </div>

            <!-- Reservation Items -->
            <Card v-if="activeItems.length > 0">
                <CardHeader>
                    <CardTitle class="text-base">Items ({{ activeItems.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div v-for="item in activeItems" :key="item.id" class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <div class="font-medium">{{ item.resource_name }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ item.resource_type_label }} • {{ item.rate_name }}
                                    <span v-if="item.duration_days"> • {{ item.duration_days }} {{ item.duration_days > 1 ? 'nights' : 'night' }}</span>
                                    <span v-if="item.duration_minutes"> • {{ Math.floor(item.duration_minutes / 60) }}h {{ item.duration_minutes % 60 }}m</span>
                                    <span v-if="item.quantity > 1"> • {{ item.quantity }}x</span>
                                </div>
                            </div>
                            <div class="text-right font-medium">
                                {{ formatCurrency(item.line_total, item.currency) }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t flex justify-between font-semibold">
                        <span>Total</span>
                        <span>{{ formatCurrency(reservation.total_amount, reservation.currency) }}</span>
                    </div>
                </CardContent>
            </Card>

            <div v-else class="p-4 border border-dashed rounded-lg text-center text-muted-foreground">
                No items added yet. Items can be added after saving the reservation.
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="7"
                test-id="update-reservation-button"
                label="Save Changes"
            />
        </template>
    </BaseFormPage>
</template>
