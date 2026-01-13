<script setup lang="ts">
import activities from '@/routes/activities';
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
import ActivityHighlightsEditor from '@/components/ActivityHighlightsEditor.vue';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

defineProps<{
    tenants?: ComboboxOption[];
}>();

const breadcrumbs = [
    { title: 'Activities', href: activities.index.url() },
    { title: 'Create Activity', href: activities.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'activity',
    action: 'create',
});

// Form fields
const selectedTenant = ref<ComboboxOption>();

const name = ref('');
const { slug } = useAutoSlug(name, {
    lowercase: true
});

const subtitle = ref('');

// Track uploaded media IDs from ImageUploader
const uploadedMediaIds = ref<number[]>([]);

// Standard Rate fields
const standardRatePrice = ref(0);
const standardRateCurrency = ref('IDR');
const standardRatePriceType = ref('flat');
const description = ref('');
const highlights = ref([]);
</script>

<template>
    <BaseFormPage
        title="Create Activity"
        :breadcrumbs="breadcrumbs"
        :action="activities.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="(data) => ({ 
            ...data, 
            uploaded_media_ids: uploadedMediaIds,
            highlights: highlights,
            subtitle: subtitle
        })"
    >
        <template #default="{ errors, processing }">
            <!-- Tenant Selection -->
            <SearchableSelect
                mode="single"
                v-model="selectedTenant"
                :options="tenants"
                :fetch-url="() => activities.create.url()"
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
                placeholder="e.g. Activity Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="3"
                autocomplete="off"
                placeholder="e.g. activity-name"
                v-model="slug"
                :error="errors.slug"
            />

            <FormField
                id="subtitle"
                label="Subtitle"
                type="text"
                :tabindex="4"
                autocomplete="off"
                placeholder="e.g. Guided Adventure"
                v-model="subtitle"
                :error="errors.subtitle"
                :optional="true"
            />

            <div class="grid gap-2">
                <Label for="description" class="flex items-center gap-1">
                    Description
                    <span class="text-muted-foreground">(Optional)</span>
                </Label>
                <Textarea
                    id="description"
                    name="description"
                    :tabindex="10"
                    placeholder="Describe this activity..."
                    v-model="description"
                    rows="12"
                />
                <InputError :message="errors.description" />
            </div>

            <ActivityHighlightsEditor
                v-model="highlights"
                :error="errors.highlights"
                :tabindex="6"
            />

            <!-- Standard Rate -->
            <div class="grid gap-4">
                <div>
                    <Label>Standard Rate</Label>
                    <p class="text-xs text-muted-foreground mt-1">
                        Set the default rate for this activity. This rate cannot be deleted but can be edited later.
                    </p>
                </div>
                
                <NumberFormField
                    id="standard_rate_price"
                    label="Price"
                    :tabindex="10"
                    placeholder="0"
                    v-model="standardRatePrice"
                    :min="0"
                    :error="errors.standard_rate_price"
                />

                <CurrencySelect
                    id="standard_rate_currency"
                    label="Currency"
                    :tabindex="10"
                    v-model="standardRateCurrency"
                    :error="errors.standard_rate_currency"
                />

                <FormField
                    id="standard_rate_price_type"
                    label="Price Type"
                    type="text"
                    :tabindex="10"
                    autocomplete="off"
                    placeholder="e.g. nightly, person, hourly"
                    v-model="standardRatePriceType"
                    :error="errors.standard_rate_price_type"
                    help-text="How this price is calculated: nightly, person, hourly, daily, session, flat, etc."
                />
            </div>

            <ImageUploader
                label="Images"
                name="images"
                :multiple="true"
                :max-files="25"
                :error="errors.images || errors.uploaded_media_ids"
                :tabindex="10"
                :disabled="processing"
                @update:uploaded-media-ids="uploadedMediaIds = $event"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="10"
                test-id="create-activity-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>