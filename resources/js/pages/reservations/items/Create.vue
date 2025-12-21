<script setup lang="ts">
import reservations from '@/routes/reservations';
import { ref, watch, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { formatNumber, formatCurrencyLabel } from '@/helpers/currency';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Resource {
    id: number;
    name: string;
    type: string;
    type_label: string;
    description: string | null;
}

interface Rate {
    id: number;
    name: string;
    price: number;
    currency: string;
    pricing_type: string;
    description: string | null;
}

interface Reservation {
    code: string;
    guest_name: string;
    tenant_id: string;
    currency: string;
}

interface Props {
    reservation: Reservation;
    resources: Resource[];
    pricingTypes: { value: string; label: string }[];
    rates?: Rate[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Reservations', href: reservations.index.url() },
    { title: props.reservation.code, href: reservations.edit.url([props.reservation.tenant_id, props.reservation.code]) },
    { title: 'Items', href: reservations.items.url([props.reservation.tenant_id, props.reservation.code]) },
    { title: 'Add Item', href: '#' },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'reservation item',
    action: 'create',
});

// Form fields
const selectedResourceId = ref<string>('');
const selectedRateId = ref<string>('');
const loadingRates = ref(false);
const availableRates = ref<Rate[]>(props.rates || []);

const selectedResource = computed(() => {
    if (!selectedResourceId.value) return null;
    const [type, id] = selectedResourceId.value.split('|');
    return props.resources.find(r => r.type === type && r.id === parseInt(id)) || null;
});

const selectedRate = computed(() => {
    if (!selectedRateId.value) return null;
    return availableRates.value.find(r => r.id === parseInt(selectedRateId.value)) || null;
});

// Pricing from selected rate (read-only)
const rate_price = computed(() => selectedRate.value?.price || 0);
const currency = computed(() => selectedRate.value?.currency || props.reservation.currency || 'IDR');
const pricing_type = computed(() => selectedRate.value?.pricing_type || 'per_night');

const quantity = ref(1);
const start_date = ref('');
const end_date = ref('');
const duration_days = ref(1);

// Fetch rates when resource changes
watch(selectedResourceId, async (newValue) => {
    selectedRateId.value = '';
    availableRates.value = [];
    
    if (!newValue) return;
    
    const [type, id] = newValue.split('|');
    loadingRates.value = true;
    
    try {
        const response = await fetch(
            reservations.items.rates.url([props.reservation.tenant_id, props.reservation.code]) + 
            `?resource_type=${encodeURIComponent(type)}&resource_id=${id}`
        );
        const data = await response.json();
        availableRates.value = data.rates || [];
    } catch (error) {
        console.error('Failed to fetch rates:', error);
        availableRates.value = [];
    } finally {
        loadingRates.value = false;
    }
});

// Calculate duration when dates change
watch([start_date, end_date], ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        const start = new Date(newStart);
        const end = new Date(newEnd);
        const diffTime = Math.abs(end.getTime() - start.getTime());
        duration_days.value = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
    }
});

// Calculate line total preview
const lineTotal = computed(() => {
    const price = rate_price.value || 0;
    const qty = quantity.value || 1;
    const days = duration_days.value || 1;

    switch (pricing_type.value) {
        case 'per_night':
            return qty * days * price;
        case 'per_person':
            return qty * price;
        case 'per_hour':
            return qty * price;
        case 'flat':
            return price;
        default:
            return qty * price;
    }
});

// Pricing type label for display
const pricingTypeLabel = computed(() => {
    const pt = props.pricingTypes.find(p => p.value === pricing_type.value);
    return pt?.label || pricing_type.value;
});

// Transform form data before submission
const transformData = (data: Record<string, any>) => {
    if (!selectedResource.value || !selectedRate.value) return data;

    return {
        ...data,
        reservable_type: selectedResource.value.type,
        reservable_id: selectedResource.value.id,
        rate_id: selectedRate.value.id,
        resource_name: selectedResource.value.name,
        resource_type_label: selectedResource.value.type_label,
        rate_name: selectedRate.value.name,
        pricing_type: pricing_type.value,
        rate_price: rate_price.value,
        currency: currency.value,
        quantity: quantity.value,
        start_date: start_date.value || null,
        end_date: end_date.value || null,
        duration_days: duration_days.value,
    };
};

// Validation - ensure rate is selected
const canSubmit = computed(() => {
    return selectedResource.value && selectedRate.value;
});
</script>

<template>
    <BaseFormPage
        title="Add Item"
        :breadcrumbs="breadcrumbs"
        :action="reservations.items.store.url([reservation.tenant_id, reservation.code])"
        method="post"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="Reservation"
                :value="`${reservation.code} - ${reservation.guest_name}`"
                help-text="Adding item to this reservation"
            />

            <!-- Resource Selection -->
            <div class="grid gap-2">
                <Label for="resource">Resource <span class="text-destructive">*</span></Label>
                <Select v-model="selectedResourceId" :disabled="processing">
                    <SelectTrigger id="resource">
                        <SelectValue placeholder="Select a resource" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="resource in resources"
                            :key="`${resource.type}|${resource.id}`"
                            :value="`${resource.type}|${resource.id}`"
                        >
                            {{ resource.name }} ({{ resource.type_label }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.reservable_id" />
            </div>

            <!-- Rate Selection -->
            <div class="grid gap-2">
                <Label for="rate">Rate <span class="text-destructive">*</span></Label>
                <Select 
                    v-model="selectedRateId" 
                    :disabled="processing || !selectedResourceId || loadingRates"
                >
                    <SelectTrigger id="rate">
                        <SelectValue :placeholder="loadingRates ? 'Loading rates...' : 'Select a rate'" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="rate in availableRates"
                            :key="rate.id"
                            :value="String(rate.id)"
                        >
                            {{ rate.name }} - {{ formatCurrencyLabel(rate.currency) }} {{ formatNumber(rate.price) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="selectedResourceId && !loadingRates && availableRates.length === 0" class="text-sm text-muted-foreground">
                    No active rates found for this resource
                </p>
                <InputError :message="errors.rate_id" />
            </div>

            <!-- Rate Info (Read-only) -->
            <div v-if="selectedRate" class="rounded-lg border bg-muted/30 p-4 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-muted-foreground">Rate Price</span>
                    <span class="font-medium">{{ formatCurrencyLabel(currency) }} {{ formatNumber(rate_price) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-muted-foreground">Pricing Type</span>
                    <span class="font-medium">{{ pricingTypeLabel }}</span>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="start_date">Start Date</Label>
                    <Input
                        id="start_date"
                        type="date"
                        v-model="start_date"
                        :disabled="processing"
                    />
                    <InputError :message="errors.start_date" />
                </div>
                <div class="grid gap-2">
                    <Label for="end_date">End Date</Label>
                    <Input
                        id="end_date"
                        type="date"
                        v-model="end_date"
                        :disabled="processing"
                    />
                    <InputError :message="errors.end_date" />
                </div>
            </div>

            <!-- Quantity and Duration -->
            <div class="grid grid-cols-2 gap-4">
                <NumberFormField
                    id="quantity"
                    label="Quantity"
                    v-model="quantity"
                    :min="1"
                    :error="errors.quantity"
                />
                <NumberFormField
                    id="duration_days"
                    label="Duration (nights/days)"
                    v-model="duration_days"
                    :min="1"
                    :error="errors.duration_days"
                />
            </div>

            <!-- Line Total Preview -->
            <div class="rounded-lg border bg-muted/50 p-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Line Total (Preview)</span>
                    <span class="text-lg font-bold">
                        {{ formatCurrencyLabel(currency) }} {{ formatNumber(lineTotal) }}
                    </span>
                </div>
                <p class="text-xs text-muted-foreground mt-1">
                    Calculated based on rate price, quantity, and duration
                </p>
            </div>

            <SubmitButton
                :processing="processing"
                :disabled="!canSubmit"
                :tabindex="10"
                test-id="add-item-button"
                label="Add Item"
            />
        </template>
    </BaseFormPage>
</template>

