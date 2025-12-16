<script setup lang="ts">
import rates from '@/routes/rates';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

defineProps<{
    tenants?: ComboboxOption[];
}>();

const breadcrumbs = [
    { title: 'Rates', href: rates.index.url() },
    { title: 'Create Rate', href: rates.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'rate',
    action: 'create',
});

// Form fields
const selectedTenant = ref<ComboboxOption>();

const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});

const description = ref('');
const price = ref(0);
const currency = ref('IDR');
const isActive = ref(true);
</script>

<template>
    <BaseFormPage
        title="Create Rate"
        :breadcrumbs="breadcrumbs"
        :action="rates.store.url()"
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
                :fetch-url="() => rates.create.url()"
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
                id="name"
                label="Name"
                type="text"
                :tabindex="2"
                autocomplete="organization"
                placeholder="e.g. Daily Rate"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="3"
                autocomplete="off"
                placeholder="e.g. daily-rate"
                v-model="slug"
                :error="errors.slug"
            />

            <FormField
                id="description"
                label="Description"
                type="textarea"
                :tabindex="4"
                placeholder="Rate description (optional)"
                v-model="description"
                :error="errors.description"
            />

            <div class="grid grid-cols-2 gap-4">
                <FormField
                    id="price"
                    label="Price"
                    type="number"
                    :tabindex="5"
                    placeholder="0"
                    v-model="price"
                    :error="errors.price"
                />

                <FormField
                    id="currency"
                    label="Currency"
                    type="text"
                    :tabindex="6"
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
                :tabindex="7"
                test-id="create-rate-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>
