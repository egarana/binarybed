<script setup lang="ts">
import activities from '@/routes/activities';
import { ref, computed, watch } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader, { type ExistingImage } from '@/components/ImageUploader.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Plus, Star } from 'lucide-vue-next';

interface Props {
    activity: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        features?: ComboboxOption[];
        images?: ExistingImage[];
    };
    features?: ComboboxOption[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Activities', href: activities.index.url() },
    { title: 'Edit Activity', href: activities.edit.url([props.activity.tenant_id, props.activity.slug]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'activity',
    action: 'update',
});

// Form fields
const selectedFeatures = ref<ComboboxOption[]>(props.activity.features || []);

// Control features visibility
const showFeatures = ref(selectedFeatures.value.length > 0);
const hasFeatures = computed(() => selectedFeatures.value.length > 0);

// Reset showFeatures if all features are removed
watch(selectedFeatures, (newFeatures) => {
    if (newFeatures.length === 0) {
        showFeatures.value = false;
    }
}, { deep: true });

const name = ref(props.activity.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.activity.slug || ''
});

// Image management
const existingImages = ref<ExistingImage[]>(props.activity.images || []);
const newImages = ref<File[]>([]);

// Transform function to prepare data for submission
function transformFormData(data: Record<string, any>) {
    return {
        ...data,
        existing_images: existingImages.value.map(img => img.id),
        new_images: newImages.value,
    };
}
</script>

<template>
    <BaseFormPage
        :title="`Edit Activity: ${activity.name}`"
        :breadcrumbs="breadcrumbs"
        :action="activities.update.url([activity.tenant_id, activity.slug])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="transformFormData"
    >
        <template #default="{ errors, processing }">
            <input type="hidden" name="tenant_id" :value="activity.tenant_id" />

            <DisabledFormField
                label="Tenant"
                :value="activity.tenant_name"
                help-text="Activity tenant cannot be changed after creation"
            />

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="organization"
                placeholder="e.g. Activity Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="2"
                autocomplete="off"
                placeholder="e.g. activity-name"
                v-model="slug"
                :error="errors.slug"
            />

            <ImageUploader
                v-model="newImages"
                :existing-images="existingImages"
                @update:existing-images="existingImages = $event"
                label="Images"
                name="images"
                :multiple="true"
                :max-files="10"
                :error="errors.images || errors.new_images || errors.existing_images"
                :tabindex="3"
                :disabled="processing"
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

                <Empty v-if="!showFeatures && !hasFeatures" class="border border-dashed">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Star />
                        </EmptyMedia>
                        <EmptyTitle>No features selected</EmptyTitle>
                        <EmptyDescription>
                            Add features to enhance your activity listing.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button type="button" variant="outline" @click="showFeatures = true">
                            <Plus /> Add Features
                        </Button>
                    </EmptyContent>
                </Empty>

                <SearchableSelect
                    v-if="showFeatures || hasFeatures"
                    mode="multiple"
                    v-model="selectedFeatures"
                    :options="features"
                    :fetch-url="() => activities.edit.url([activity.tenant_id, activity.slug])"
                    response-key="features"
                    search-param="search"
                    label="Features"
                    placeholder="Select features"
                    search-placeholder="Search features..."
                    name="features"
                    :tabindex="4"
                    :error="errors.features"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />

                <!-- Flag to indicate features have been cleared (for empty array detection) -->
                <input
                    v-if="selectedFeatures.length === 0"
                    type="hidden"
                    name="_features_cleared"
                    value="1"
                />
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="5"
                test-id="update-activity-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>