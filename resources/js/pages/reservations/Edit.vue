<script setup lang="ts">
import reservations from '@/routes/reservations';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import PhoneInput from '@/components/PhoneInput.vue';
import InputError from '@/components/InputError.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';



interface Props {
    reservation: {
        id: number;
        code: string;
        guest_name: string;
        guest_email: string;
        guest_phone: { country: { country: string; countryName: string; code: string }; number: string } | null;
        status: string;
        subtotal: number;
        total_amount: number;
        currency: string;
        notes: string | null;
        cancellation_reason: string | null;
        tenant_id: string;
        tenant_name: string;

        created_at: string;
        updated_at: string;
    };
    statuses: string[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Reservations', href: reservations.index.url() },
    { title: `${props.reservation.code}`, href: reservations.edit.url([props.reservation.tenant_id, props.reservation.code]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'reservation',
    action: 'update',
});

// Form fields
const guestName = ref(props.reservation.guest_name);
const guestEmail = ref(props.reservation.guest_email);
const guestPhone = ref<{ country: { country: string; countryName: string; code: string }; number: string } | null>(props.reservation.guest_phone);
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

            <DisabledFormField
                label="Tenant"
                :value="reservation.tenant_name"
                help-text="The tenant cannot be changed after the reservation has been made"
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
                :required="true"
            />

            <PhoneInput
                name="guest_phone"
                label="Phone"
                :tabindex="3"
                v-model="guestPhone"
                :error="errors.guest_phone"
                :required="true"
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
                <Label for="notes">Notes (Optional)</Label>
                <Textarea
                    id="notes"
                    name="notes"
                    :tabindex="6"
                    placeholder="Add any special requests or notes..."
                    v-model="notes"
                    rows="6"
                />
                <InputError :message="errors.notes" />
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
