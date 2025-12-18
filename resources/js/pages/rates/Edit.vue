<script setup lang="ts">
import rates from '@/routes/rates';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import CurrencyFormField from '@/components/CurrencyFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Item, ItemContent, ItemTitle, ItemDescription, ItemActions } from '@/components/ui/item';
import InputError from '@/components/InputError.vue';

interface Props {
    rate: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        resource_name: string;
        resource_slug: string;
        rateable_type: string;
        product_display: string;
        name: string;
        slug: string;
        description: string | null;
        price: number;
        currency: string;
        is_active: boolean;
        is_default: boolean;
    };
    returnTo?: string;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Rates', href: rates.index.url() },
    { title: 'Edit Rate', href: rates.edit.url([props.rate.tenant_id, props.rate.resource_slug, props.rate.slug]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'rate',
    action: 'update',
});

// Form fields
const name = ref(props.rate.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.rate.slug || ''
});

const description = ref(props.rate.description || '');
const price = ref(props.rate.price || 0);
const currency = ref(props.rate.currency || 'IDR');
const isActive = ref(props.rate.is_active ?? true);

// Transform form data before submission
const transformData = (data: Record<string, any>) => ({
    ...data,
    tenant_id: props.rate.tenant_id,
    is_active: isActive.value ? '1' : '0',
    return: props.returnTo || '0',
});
</script>

<template>
    <BaseFormPage
        :title="`Edit Rate: ${rate.name}`"
        :breadcrumbs="breadcrumbs"
        :action="rates.update.url([rate.tenant_id, rate.resource_slug, rate.slug])"
        method="put"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <!-- Validation Errors Debugger -->
            <div v-if="Object.keys(errors).length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm font-medium text-red-800 mb-2">🐛 Validation Errors:</p>
                <pre class="text-xs text-red-700 overflow-auto">{{ JSON.stringify(errors, null, 2) }}</pre>
            </div>

            <DisabledFormField
                label="Product"
                :value="rate.product_display"
                help-text="Rate product cannot be changed after creation"
            />

            <!-- Active Switch -->
            <Item variant="outline">
                <ItemContent>
                    <ItemTitle>Active</ItemTitle>
                    <ItemDescription>When enabled, this rate will be visible and can be applied to bookings. Disable to temporarily hide without deleting.</ItemDescription>
                </ItemContent>
                <ItemActions>
                    <Switch id="is_active" v-model="isActive" />
                </ItemActions>
            </Item>

            <!-- Name Field -->
            <DisabledFormField
                v-if="rate.is_default"
                label="Name"
                :value="rate.name"
                help-text="Default rate name cannot be changed"
            />
            <FormField
                v-else
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="organization"
                placeholder="e.g. Daily Rate"
                v-model="name"
                :error="errors.name"
            />

            <!-- Slug Field -->
            <DisabledFormField
                v-if="rate.is_default"
                label="Slug"
                :value="rate.slug"
                help-text="Default rate slug cannot be changed"
            />
            <FormField
                v-else
                id="slug"
                label="Slug"
                type="text"
                :tabindex="2"
                autocomplete="off"
                placeholder="e.g. daily-rate"
                v-model="slug"
                :error="errors.slug"
            />

            <!-- Description -->
            <div class="grid gap-2">
                <Label for="description">Description (Optional)</Label>
                <Textarea
                    id="description"
                    name="description"
                    :tabindex="3"
                    placeholder="Describe this rate plan, including what's included, terms, or special conditions..."
                    v-model="description"
                    rows="6"
                />
                <InputError :message="errors.description" />
            </div>

            <NumberFormField
                id="price"
                label="Price"
                :tabindex="4"
                placeholder="0"
                v-model="price"
                :min="0"
                :error="errors.price"
            />

            <!-- Currency -->
            <CurrencyFormField
                id="currency"
                label="Currency"
                :tabindex="5"
                placeholder="IDR"
                v-model="currency"
                :error="errors.currency"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="6"
                test-id="update-rate-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>
