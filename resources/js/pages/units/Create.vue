<script setup lang="ts">
import units from '@/routes/units';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import CurrencySelect from '@/components/CurrencySelect.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    tenants?: ComboboxOption[];
}>();

const breadcrumbs = [
    { title: 'Units', href: units.index.url() },
    { title: 'Create Unit', href: units.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'unit',
    action: 'create',
});

// Form fields
const selectedTenant = ref<ComboboxOption>();

const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});

// Track uploaded media IDs from ImageUploader
const uploadedMediaIds = ref<number[]>([]);

// Standard Rate fields
const standardRatePrice = ref(0);
const standardRateCurrency = ref('IDR');
const standardRatePriceType = ref('flat');

</script>

<template>
    <BaseFormPage
        title="Create Unit"
        :breadcrumbs="breadcrumbs"
        :action="units.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="(data) => ({ 
            ...data, 
            uploaded_media_ids: uploadedMediaIds
        })"
    >
        <template #default="{ errors, processing }">
            <!-- Tenant Selection -->
            <SearchableSelect
                mode="single"
                v-model="selectedTenant"
                :options="tenants"
                :fetch-url="() => units.create.url()"
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
                placeholder="e.g. Unit Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="3"
                autocomplete="off"
                placeholder="e.g. unit-name"
                v-model="slug"
                :error="errors.slug"
            />

            <ImageUploader
                label="Images"
                name="images"
                :multiple="true"
                :max-files="25"
                :error="errors.images || errors.uploaded_media_ids"
                :tabindex="5"
                :disabled="processing"
                @update:uploaded-media-ids="uploadedMediaIds = $event"
            />

            <!-- Standard Rate -->
            <div class="grid gap-4">
                <div>
                    <Label>Standard Rate</Label>
                    <p class="text-xs text-muted-foreground mt-1">
                        Set the default rate for this unit. This rate cannot be deleted but can be edited later.
                    </p>
                </div>
                
                <NumberFormField
                    id="standard_rate_price"
                    label="Price"
                    :tabindex="6"
                    placeholder="0"
                    v-model="standardRatePrice"
                    :min="0"
                    :error="errors.standard_rate_price"
                />

                <CurrencySelect
                    id="standard_rate_currency"
                    label="Currency"
                    :tabindex="6"
                    v-model="standardRateCurrency"
                    :error="errors.standard_rate_currency"
                />

                <FormField
                    id="standard_rate_price_type"
                    label="Price Type"
                    type="text"
                    :tabindex="7"
                    autocomplete="off"
                    placeholder="e.g. nightly, person, hourly"
                    v-model="standardRatePriceType"
                    :error="errors.standard_rate_price_type"
                    help-text="How this price is calculated: nightly, person, hourly, daily, session, flat, etc."
                />
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="8"
                test-id="create-unit-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>