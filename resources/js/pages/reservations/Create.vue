<script setup lang="ts">
import reservations from '@/routes/reservations';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

defineProps<{
    tenants?: ComboboxOption[];
    statuses?: string[];
}>();

const breadcrumbs = [
    { title: 'Reservations', href: reservations.index.url() },
    { title: 'Create Reservation', href: reservations.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'reservation',
    action: 'create',
});

// Form fields
const selectedTenant = ref<ComboboxOption>();
const guestName = ref('');
const guestEmail = ref('');
const guestPhone = ref('');
const notes = ref('');
</script>

<template>
    <BaseFormPage
        title="Create Reservation"
        :breadcrumbs="breadcrumbs"
        :action="reservations.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <!-- Tenant Selection -->
            <SearchableSelect
                mode="single"
                v-model="selectedTenant"
                :options="tenants"
                :fetch-url="() => reservations.create.url()"
                response-key="tenants"
                label="Tenant"
                placeholder="Select a tenant"
                search-placeholder="Search tenant..."
                name="tenant_id"
                :tabindex="1"
                :error="errors.tenant_id"
                :required="true"
                :clearable="true"
                :disabled="processing"
            />

            <FormField
                id="guest_name"
                label="Guest Name"
                type="text"
                :tabindex="2"
                autocomplete="name"
                placeholder="e.g. John Doe"
                v-model="guestName"
                :error="errors.guest_name"
            />

            <FormField
                id="guest_email"
                label="Email"
                type="email"
                :tabindex="3"
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
                :tabindex="4"
                autocomplete="tel"
                placeholder="e.g. +628123456789"
                v-model="guestPhone"
                :error="errors.guest_phone"
                :required="false"
            />

            <div class="grid gap-2">
                <Label for="notes">Notes <span class="text-muted-foreground">(Optional)</span></Label>
                <Textarea
                    id="notes"
                    name="notes"
                    v-model="notes"
                    placeholder="Add any special requests or notes..."
                    :tabindex="5"
                    :disabled="processing"
                    rows="3"
                />
                <p v-if="errors.notes" class="text-sm text-destructive">{{ errors.notes }}</p>
            </div>

            <p class="text-sm text-muted-foreground">
                <strong>Note:</strong> Reservation code will be generated automatically. 
                You can add items after creating the reservation.
            </p>

            <SubmitButton
                :processing="processing"
                :tabindex="6"
                test-id="create-reservation-button"
                label="Create Reservation"
            />
        </template>
    </BaseFormPage>
</template>
