<script setup lang="ts">
import units from '@/routes/units';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import CurrencySelect from '@/components/CurrencySelect.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        commission_config?: {
            commission_type: string;
            commission_percentage: number | null;
            commission_fixed: number | null;
            currency: string | null;
        } | null;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Units', href: units.index.url() },
    { title: props.unit.name, href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
    { title: 'Commission', href: '#' },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'commission',
    action: 'update',
});

// Commission config fields - load from existing data
const commissionType = ref<string>(props.unit.commission_config?.commission_type || 'percentage');
const commissionPercentage = ref<number>(props.unit.commission_config?.commission_percentage || 5);
const commissionFixed = ref<number>(props.unit.commission_config?.commission_fixed || 0);
const currency = ref<string>(props.unit.commission_config?.currency || 'IDR');

// Computed for unified amount input
const commissionAmount = computed({
    get: () => commissionType.value === 'percentage' ? commissionPercentage.value : commissionFixed.value,
    set: (val: number) => {
        if (commissionType.value === 'percentage') {
            commissionPercentage.value = val;
        } else {
            commissionFixed.value = val;
        }
    }
});

// Transform form data before submission
const transformData = (data: Record<string, any>) => ({
    ...data,
    commission_type: commissionType.value || null,
    commission_percentage: commissionType.value === 'percentage' ? commissionPercentage.value : null,
    commission_fixed: commissionType.value === 'fixed' ? commissionFixed.value : null,
    currency: commissionType.value === 'fixed' ? currency.value : null,
});
</script>

<template>
    <BaseFormPage
        title="Commission"
        :breadcrumbs="breadcrumbs"
        :action="units.commission.update.url([unit.tenant_id, unit.slug])"
        method="post"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="Product"
                :value="`${unit.name} - ${unit.tenant_name}`"
                help-text="Commission configuration for this product"
            />

            <!-- Commission Config -->
            <div class="grid gap-2">
                <Label>Commission Type</Label>
                <Select v-model="commissionType" :disabled="processing">
                    <SelectTrigger>
                        <SelectValue placeholder="Select type (optional)" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="percentage">
                            Percentage
                        </SelectItem>
                        <SelectItem value="fixed">
                            Fixed Amount
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Currency selector for fixed -->
            <CurrencySelect
                v-if="commissionType === 'fixed'"
                id="currency"
                v-model="currency"
                :disabled="processing"
                :error="errors.currency"
            />

            <NumberFormField
                id="commission_amount"
                :label="commissionType === 'percentage' ? 'Commission Percentage (%)' : `Commission Amount (${currency})`"
                :tabindex="1"
                :placeholder="commissionType === 'percentage' ? '5' : '100000'"
                v-model="commissionAmount"
                :min="0"
                :max="commissionType === 'percentage' ? 100 : 10000000"
                :step="commissionType === 'percentage' ? 1 : 10000"
                :error="commissionType === 'percentage' ? errors.commission_percentage : errors.commission_fixed"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="2"
                test-id="update-commission-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>



