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
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Textarea } from '@/components/ui/textarea';
import { formatNumber, formatCurrencyLabel } from '@/helpers/currency';
import { formatDuration, formatDurationDays } from '@/helpers/duration';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { getLocalTimeZone, type DateValue } from '@internationalized/date';
import dayjs from 'dayjs';
import { CalendarIcon } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table'

interface Product {
    id: number;
    name: string;
    type: string;
    type_label: string;
    description: string | null;
    rates_count: number;
}

interface Rate {
    id: number;
    name: string;
    price: number;
    currency: string;
    price_type: string;
    description: string | null;
}

interface Reservation {
    code: string;
    guest_name: string;
    tenant_id: string;
    tenant_name: string;
    currency: string;
}

interface Props {
    reservation: Reservation;
    products: Product[];
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
const selectedProductId = ref<string>('');
const selectedRateId = ref<string>('');
const loadingRates = ref(false);
const availableRates = ref<Rate[]>(props.rates || []);

const selectedProduct = computed(() => {
    if (!selectedProductId.value) return null;
    const [type, id] = selectedProductId.value.split('|');
    return props.products.find(r => r.type === type && r.id === parseInt(id)) || null;
});

const selectedRate = computed(() => {
    if (!selectedRateId.value) return null;
    return availableRates.value.find(r => r.id === parseInt(selectedRateId.value)) || null;
});

// Product type detection
const isUnit = computed(() => selectedProduct.value?.type === 'App\\Models\\Unit');
const isActivity = computed(() => selectedProduct.value?.type === 'App\\Models\\Activity');

// Field enable/disable control
const isDateRangeEnabled = computed(() => !!selectedProduct.value);
const isTimeRangeEnabled = computed(() => isActivity.value); // Only for Activity

// Pricing from selected rate (read-only)
const rate_price = computed(() => selectedRate.value?.price || 0);
const currency = computed(() => selectedRate.value?.currency || props.reservation.currency || 'IDR');
const price_type = computed(() => selectedRate.value?.price_type || 'night');

const quantity = ref(1);
const start_date = ref<DateValue>();
const end_date = ref<DateValue>();
const start_time = ref('flexible');
const end_time = ref('flexible');
const duration_days = ref(1);
const duration_minutes = ref<number>(0);
const product_description = ref('');
const rate_description = ref('');

// Generate time options with 'Flexible' as first option
const timeOptions = computed(() => {
    const options = ['flexible']; // Add flexible as first option
    for (let hour = 0; hour < 24; hour++) {
        for (let minute = 0; minute < 60; minute += 30) {
            const hourStr = hour.toString().padStart(2, '0');
            const minuteStr = minute.toString().padStart(2, '0');
            options.push(`${hourStr}:${minuteStr}`);
        }
    }
    return options;
});

// Check if time is flexible
const isTimeFlexible = computed(() => start_time.value === 'flexible' || end_time.value === 'flexible');

// Format date for API submission (convert DateValue to YYYY-MM-DD string)
const formatDateForApi = (date: DateValue | undefined): string | null => {
    return date ? dayjs(date.toDate(getLocalTimeZone())).format('YYYY-MM-DD') : null;
};

// Fetch rates when product changes
watch(selectedProductId, async (newValue) => {
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
watch([start_date, end_date, isUnit], ([newStart, newEnd, unitType]) => {
    if (newStart && newEnd) {
        const start = newStart.toDate(getLocalTimeZone());
        const end = newEnd.toDate(getLocalTimeZone());
        const diffTime = end.getTime() - start.getTime();
        
        // If end is before start, duration is 0 (invalid range)
        if (diffTime < 0) {
            duration_days.value = 0;
            return;
        }
        
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        // Unit (Accommodation): count nights between dates
        // Activity: count days inclusive (start date to end date)
        if (unitType) {
            // For Unit: nights = difference in days (Dec 23-26 = 3 nights)
            duration_days.value = Math.max(1, diffDays);
        } else {
            // For Activity: days = difference + 1 for inclusive (Dec 23-26 = 4 days)
            duration_days.value = diffDays + 1;
        }
    } else {
        duration_days.value = 0;
    }
});

// For Activity: Auto-set end_date to start_date (single-day activities)
watch(start_date, (newStart) => {
    if (isActivity.value && newStart) {
        end_date.value = newStart;
    }
});

// Reset time values to 'flexible' when switching to Unit (time is disabled for Unit)
watch(isUnit, (newIsUnit) => {
    if (newIsUnit) {
        start_time.value = 'flexible';
        end_time.value = 'flexible';
    }
});

// Calculate duration in minutes when times change
watch([start_time, end_time], ([newStart, newEnd]) => {
    // If either time is 'flexible', don't calculate duration
    if (newStart === 'flexible' || newEnd === 'flexible') {
        duration_minutes.value = 0;
        return;
    }
    
    if (newStart && newEnd) {
        const [startHour, startMin] = newStart.split(':').map(Number);
        const [endHour, endMin] = newEnd.split(':').map(Number);
        
        const startMinutes = startHour * 60 + startMin;
        const endMinutes = endHour * 60 + endMin;
        
        const diff = endMinutes - startMinutes;
        // If end time is before start time, duration is 0 (no overnight allowed)
        duration_minutes.value = diff < 0 ? 0 : diff;
    } else {
        duration_minutes.value = 0;
    }
});

// Calculate line total preview
// All pricing types use: quantity × duration_days × rate_price
const lineTotal = computed(() => {
    const price = rate_price.value || 0;
    const qty = quantity.value || 1;
    const days = duration_days.value || 1;

    return qty * days * price;
});

// Transform form data before submission - returns all data explicitly
const transformData = () => {
    return {
        reservable_type: selectedProduct.value?.type || '',
        reservable_id: selectedProduct.value?.id || '',
        rate_id: selectedRate.value?.id || '',
        resource_name: selectedProduct.value?.name || '',
        resource_type_label: selectedProduct.value?.type_label || '',
        rate_name: selectedRate.value?.name || '',
        price_type: price_type.value,
        rate_price: rate_price.value,
        currency: currency.value,
        quantity: quantity.value,
        start_date: formatDateForApi(start_date.value),
        end_date: formatDateForApi(end_date.value),
        start_time: start_time.value === 'flexible' ? null : (start_time.value || null),
        end_time: end_time.value === 'flexible' ? null : (end_time.value || null),
        duration_days: duration_days.value,
        duration_minutes: isTimeFlexible.value ? null : (duration_minutes.value || null),
        resource_description: product_description.value || selectedProduct.value?.description || null,
        rate_description: rate_description.value || selectedRate.value?.description || null,
    };
};

// Validation - ensure rate is selected
const canSubmit = computed(() => {
    return selectedProduct.value && selectedRate.value;
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

            <DisabledFormField
                label="Tenant"
                :value="reservation.tenant_name"
                help-text="Tenant that owns this reservation"
            />

            <!-- Product Selection -->
            <div class="grid gap-2">
                <Label for="product">Product</Label>
                <Select v-model="selectedProductId" :disabled="processing">
                    <SelectTrigger id="product">
                        <SelectValue placeholder="Select a product" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="product in products"
                            :key="`${product.type}|${product.id}`"
                            :value="`${product.type}|${product.id}`"
                        >
                            {{ product.name }} ({{ product.type_label }}) - {{ product.rates_count }} rates
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.reservable_id" />
            </div>

            <!-- Rate Selection -->
            <div class="grid gap-2">
                <Label for="rate">Rate</Label>
                <Select 
                    v-model="selectedRateId" 
                    :disabled="processing || !selectedProductId || loadingRates"
                >
                    <SelectTrigger id="rate" class="disabled:bg-muted disabled:text-muted-foreground">
                        <SelectValue :placeholder="loadingRates ? 'Loading rates...' : 'Select a rate'" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="rate in availableRates"
                            :key="rate.id"
                            :value="String(rate.id)"
                        >
                            {{ rate.name }} - {{ formatCurrencyLabel(rate.currency) }} {{ formatNumber(rate.price) }}<span v-if="rate.price_type && rate.price_type !== 'flat'">/{{ rate.price_type }}</span>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="selectedProductId && !loadingRates && availableRates.length === 0" class="text-sm text-muted-foreground">
                    No active rates found for this product
                </p>
                <InputError :message="errors.rate_id" />
            </div>

            <!-- Start Date & Time Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label>Start Date</Label>
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                :class="cn(
                                    'justify-start text-left font-normal',
                                    !start_date && 'text-muted-foreground',
                                )"
                                :disabled="processing || !isDateRangeEnabled"
                                class="disabled:bg-muted disabled:text-muted-foreground"
                            >
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                {{ start_date ? dayjs(start_date.toDate(getLocalTimeZone())).format('MMM DD, YYYY') : 'Pick a date' }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar v-model="start_date" initial-focus />
                        </PopoverContent>
                    </Popover>
                </div>
                <div class="grid gap-2">
                    <Label for="start_time">Start Time</Label>
                    <Select v-model="start_time" :disabled="processing || !isTimeRangeEnabled">
                        <SelectTrigger id="start_time" class="disabled:bg-muted disabled:text-muted-foreground">
                            <SelectValue placeholder="Select start time" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="time in timeOptions"
                                :key="time"
                                :value="time"
                            >
                                {{ time === 'flexible' ? 'Flexible' : time }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- End Date & Time Row -->
            <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                <div class="grid gap-2">
                    <Label>End Date</Label>
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                :class="cn(
                                    'justify-start text-left font-normal',
                                    !end_date && 'text-muted-foreground',
                                )"
                                :disabled="processing || !isDateRangeEnabled"
                                class="disabled:bg-muted disabled:text-muted-foreground"
                            >
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                {{ end_date ? dayjs(end_date.toDate(getLocalTimeZone())).format('MMM DD, YYYY') : 'Pick a date' }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar v-model="end_date" initial-focus />
                        </PopoverContent>
                    </Popover>
                </div>
                <div class="grid gap-2">
                    <Label for="end_time">End Time</Label>
                    <Select v-model="end_time" :disabled="processing || !isTimeRangeEnabled">
                        <SelectTrigger id="end_time" class="disabled:bg-muted disabled:text-muted-foreground">
                            <SelectValue placeholder="Select end time" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="time in timeOptions"
                                :key="time"
                                :value="time"
                            >
                                {{ time === 'flexible' ? 'Flexible' : time }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="col-span-2">
                    <!-- Schedule Error (unified date/time validation) -->
                    <InputError :message="errors.schedule" />
                </div>
            </div>

            <!-- Quantity and Duration -->
            <div class="grid grid-cols-3 gap-4">
                <NumberFormField
                    id="quantity"
                    label="Quantity"
                    v-model="quantity"
                    :min="1"
                    :error="errors.quantity"
                />
                <DisabledFormField
                    label="Duration (Days)"
                    :value="formatDurationDays(duration_days, isUnit)"
                />
                <DisabledFormField
                    label="Duration (Time)"
                    :value="isTimeFlexible ? 'Flexible' : formatDuration(duration_minutes)"
                />
            </div>

            <!-- Product Description (Optional) -->
            <div class="grid gap-2">
                <Label for="product_description">Product Description (Optional)</Label>
                <Textarea
                    id="product_description"
                    name="product_description"
                    placeholder="Additional details about the product..."
                    v-model="product_description"
                    rows="4"
                />
                <InputError :message="errors.resource_description" />
            </div>

            <!-- Rate Description (Optional) -->
            <div class="grid gap-2">
                <Label for="rate_description">Rate Description (Optional)</Label>
                <Textarea
                    id="rate_description"
                    name="rate_description"
                    placeholder="Additional details about the rate..."
                    v-model="rate_description"
                    rows="4"
                />
                <InputError :message="errors.rate_description" />
            </div>

            <!-- Pricing Summary Preview -->
            <div class="grid gap-2">
                <div class="text-sm font-medium mb-0.5">Pricing Summary (Preview)</div>
                <div class="mx-auto w-full">
                    <div class="overflow-hidden rounded-md border">
                        <Table>
                            <TableBody>
                                <!-- Product -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r">
                                    <TableCell class="w-44 whitespace-nowrap bg-muted/50 px-3 py-3 font-medium">Product</TableCell>
                                    <TableCell class="px-3 py-3 text-right">
                                        <span v-if="selectedProduct">{{ selectedProduct.name }}</span>
                                        <span v-else class="text-muted-foreground italic">No item selected</span>
                                    </TableCell>
                                </TableRow>
                                
                                <!-- Rate -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r">
                                    <TableCell class="w-44 whitespace-nowrap bg-muted/50 px-3 py-3 font-medium">Rate</TableCell>
                                    <TableCell class="px-3 py-3 text-right">
                                        <span v-if="selectedRate">{{ selectedRate.name }}</span>
                                        <span v-else class="text-muted-foreground italic">No item selected</span>
                                    </TableCell>
                                </TableRow>
                                
                                <!-- Unit Price -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r">
                                    <TableCell class="w-44 whitespace-nowrap bg-muted/50 px-3 py-3 font-medium">Unit Price</TableCell>
                                    <TableCell class="px-3 py-3 text-right">
                                        <span v-if="selectedRate">{{ formatCurrencyLabel(currency) }} {{ formatNumber(rate_price) }}<span v-if="price_type && price_type !== 'flat'" class="text-muted-foreground">/<span class="text-xs">{{ price_type }}</span></span></span>
                                        <span v-else class="text-muted-foreground italic">No item selected</span>
                                    </TableCell>
                                </TableRow>
                                
                                <!-- Quantity -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r">
                                    <TableCell class="w-44 whitespace-nowrap bg-muted/50 px-3 py-3 font-medium">Quantity</TableCell>
                                    <TableCell class="px-3 py-3 text-right">{{ quantity }}</TableCell>
                                </TableRow>
                                
                                <!-- Duration (Days) -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r">
                                    <TableCell class="w-44 whitespace-nowrap bg-muted/50 px-3 py-3 font-medium">Duration (Days)</TableCell>
                                    <TableCell class="px-3 py-3 text-right">{{ formatDurationDays(duration_days, isUnit) }}</TableCell>
                                </TableRow>
                                
                                <!-- Duration (Time) -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r">
                                    <TableCell class="w-44 whitespace-nowrap bg-muted/50 px-3 py-3 font-medium">Duration (Time)</TableCell>
                                    <TableCell class="px-3 py-3 text-right">{{ isTimeFlexible ? 'Flexible' : formatDuration(duration_minutes) }}</TableCell>
                                </TableRow>
                                
                                <!-- Line Total -->
                                <TableRow class="*:border-border [&>:not(:last-child)]:border-r bg-primary/5">
                                    <TableCell colspan="2" class="bg-muted/50 px-3 py-4 font-semibold">
                                        <div class="flex items-center justify-between">
                                            <div>Line Total</div>
                                            <div>
                                                {{ formatCurrencyLabel(currency) }} {{ formatNumber(lineTotal) }}
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
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

