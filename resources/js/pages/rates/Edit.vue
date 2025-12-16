<script setup lang="ts">
import rates from '@/routes/rates';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface Props {
    rate: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        description: string | null;
        price: number;
        currency: string;
        is_active: boolean;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Rates', href: rates.index.url() },
    { title: 'Edit Rate', href: rates.edit.url([props.rate.tenant_id, props.rate.slug]) },
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
</script>

<template>
    <BaseFormPage
        :title="`Edit Rate: ${rate.name}`"
        :breadcrumbs="breadcrumbs"
        :action="rates.update.url([rate.tenant_id, rate.slug])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <input type="hidden" name="tenant_id" :value="rate.tenant_id" />

            <DisabledFormField
                label="Tenant"
                :value="rate.tenant_name"
                help-text="Rate tenant cannot be changed after creation"
            />

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

            <FormField
                id="description"
                label="Description"
                type="textarea"
                :tabindex="3"
                placeholder="Rate description (optional)"
                v-model="description"
                :error="errors.description"
            />

            <div class="grid grid-cols-2 gap-4">
                <FormField
                    id="price"
                    label="Price"
                    type="number"
                    :tabindex="4"
                    placeholder="0"
                    v-model="price"
                    :error="errors.price"
                />

                <FormField
                    id="currency"
                    label="Currency"
                    type="text"
                    :tabindex="5"
                    placeholder="IDR"
                    v-model="currency"
                    :error="errors.currency"
                />
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox id="is_active" v-model:checked="isActive" name="is_active" />
                <Label for="is_active" class="cursor-pointer">Active</Label>
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="6"
                test-id="update-rate-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>
