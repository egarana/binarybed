<script setup lang="ts">
import activities from '@/routes/activities';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import CurrencyFormField from '@/components/CurrencyFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Plus, Star } from 'lucide-vue-next';

defineProps<{
    tenants?: ComboboxOption[];
    features?: ComboboxOption[];
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
const selectedFeatures = ref<ComboboxOption[]>([]);

// Ref for SearchableSelect
const featuresSelectRef = ref<InstanceType<typeof SearchableSelect>>();

// Control features visibility - only hide empty state when features are selected
const hasFeatures = computed(() => selectedFeatures.value.length > 0);

// Open features dropdown
const openFeaturesDropdown = () => {
    featuresSelectRef.value?.open();
};

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
            standard_rate_price: standardRatePrice,
            standard_rate_currency: standardRateCurrency
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


            <!-- Features Selection -->
            <div class="grid gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <Label>Features <span class="text-muted-foreground">(Optional)</span></Label>
                        <p class="text-xs text-muted-foreground mt-1">
                            Add features to highlight your activity's unique characteristics and offerings.
                        </p>
                    </div>
                </div>

                <SearchableSelect
                    ref="featuresSelectRef"
                    mode="multiple"
                    v-model="selectedFeatures"
                    :options="features"
                    :fetch-url="() => activities.create.url()"
                    response-key="features"
                    search-param="search"
                    label="Features"
                    placeholder="Select features"
                    search-placeholder="Search features..."
                    name="features"
                    :tabindex="5"
                    :error="errors.features"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />

                <Empty 
                    v-if="!hasFeatures" 
                    class="border border-dashed cursor-pointer hover:border-primary/50 transition-colors"
                    @click="openFeaturesDropdown"
                >
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Star />
                        </EmptyMedia>
                        <EmptyTitle>No features selected</EmptyTitle>
                        <EmptyDescription>
                            Click to add features to enhance your activity listing.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button type="button" variant="outline" @click.stop="openFeaturesDropdown">
                            <Plus /> Add Features
                        </Button>
                    </EmptyContent>
                </Empty>

                <!-- Flag to indicate features have been cleared (for empty array detection) -->
                <input
                    v-if="selectedFeatures.length === 0"
                    type="hidden"
                    name="_features_cleared"
                    value="1"
                />
            </div>

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
                        Set the default rate for this activity. This rate cannot be deleted but can be edited later.
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

                <CurrencyFormField
                    id="standard_rate_currency"
                    label="Currency"
                    :tabindex="7"
                    placeholder="IDR"
                    v-model="standardRateCurrency"
                    :error="errors.standard_rate_currency"
                />
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="8"
                test-id="create-activity-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>