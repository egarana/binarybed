<script setup lang="ts">
import units from '@/routes/units';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
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
    { title: 'Units', href: units.index.url() },
    { title: 'Create Unit', href: units.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'unit',
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

</script>

<template>
    <BaseFormPage
        title="Create Unit"
        :breadcrumbs="breadcrumbs"
        :action="units.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="(data) => ({ ...data, uploaded_media_ids: uploadedMediaIds })"
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


            <!-- Features Selection -->
            <div class="grid gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <Label>Features <span class="text-muted-foreground">(Optional)</span></Label>
                        <p class="text-xs text-muted-foreground mt-1">
                            Add features to highlight your unit's amenities and characteristics.
                        </p>
                    </div>
                </div>

                <SearchableSelect
                    ref="featuresSelectRef"
                    mode="multiple"
                    v-model="selectedFeatures"
                    :options="features"
                    :fetch-url="() => units.create.url()"
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
                            Click to add features to enhance your unit listing.
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

            <SubmitButton
                :processing="processing"
                :tabindex="6"
                test-id="create-unit-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>