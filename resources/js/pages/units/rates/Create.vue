<script setup lang="ts">
import units from '@/routes/units';
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
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
    };
    productDisplay: string;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Units', href: units.index.url() },
    { title: props.unit.name, href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
    { title: 'Rates', href: units.rates.url([props.unit.tenant_id, props.unit.slug]) },
    { title: 'Create Rate', href: '#' },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'rate',
    action: 'create',
});

// Form fields
const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});

const description = ref('');
const price = ref(0);
const currency = ref('IDR');
const isActive = ref(true);

// Transform form data before submission
const transformData = (data: Record<string, any>) => ({
    ...data,
    tenant_id: props.unit.tenant_id,
    rateable_type: 'App\\Models\\Unit',
    rateable_id: props.unit.id,
    redirect_to_resource: '1',
    is_active: isActive.value ? '1' : '0',
});
</script>

<template>
    <BaseFormPage
        title="Create Rate"
        :breadcrumbs="breadcrumbs"
        :action="rates.store.url()"
        method="post"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="Product"
                :value="productDisplay"
                help-text="This rate will be created for this unit"
            />

            <!-- Active Switch -->
            <Item variant="outline">
                <ItemContent>
                    <ItemTitle>Active</ItemTitle>
                    <ItemDescription>When enabled, this rate will be visible and can be applied to bookings. Disable to temporarily hide without deleting.</ItemDescription>
                </ItemContent>
                <ItemActions>
                    <Switch id="is_active" v-model="isActive" :default-checked="true" />
                </ItemActions>
            </Item>

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="organization"
                placeholder="e.g. Daily Rate"
                v-model="name"
                :error="errors.name"
            />

            <FormField
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
                test-id="create-rate-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>
