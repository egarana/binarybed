<script setup lang="ts">
import units from '@/routes/units';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader, { type ExistingImage } from '@/components/ImageUploader.vue';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        description?: string;
        images?: ExistingImage[];
        // New fields
        subtitle?: string;
        max_guests?: number;
        bedroom_count?: number;
        bathroom_count?: number;
        view?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Units', href: units.index.url() },
    { title: 'Edit Unit', href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'unit',
    action: 'update',
});

const name = ref(props.unit.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.unit.slug || ''
});

// Image management
const existingImages = ref<ExistingImage[]>(props.unit.images || []);
const uploadedMediaIds = ref<number[]>([]);
const description = ref(props.unit.description || '');

// Unit Capacity & Details
const subtitle = ref(props.unit.subtitle ?? '');
const maxGuests = ref(props.unit.max_guests ?? 2);
const bedroomCount = ref(props.unit.bedroom_count ?? 1);
const bathroomCount = ref(props.unit.bathroom_count ?? 1);
const view = ref(props.unit.view ?? '');

// Transform function to prepare data for submission
function transformFormData(data: Record<string, any>) {
    return {
        ...data,
        existing_images: existingImages.value.map(img => img.id),
        uploaded_media_ids: uploadedMediaIds.value,
    };
}
</script>

<template>
    <BaseFormPage
        :title="`Edit Unit: ${unit.name}`"
        :breadcrumbs="breadcrumbs"
        :action="units.update.url([unit.tenant_id, unit.slug])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="transformFormData"
    >
        <template #default="{ errors, processing }">
            <input type="hidden" name="tenant_id" :value="unit.tenant_id" />

            <DisabledFormField
                label="Tenant"
                :value="unit.tenant_name"
                help-text="Unit tenant cannot be changed after creation"
            />

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="organization"
                placeholder="e.g. Unit Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="2"
                autocomplete="off"
                placeholder="e.g. unit-name"
                v-model="slug"
                :error="errors.slug"
            />

            <FormField
                id="subtitle"
                label="Subtitle"
                type="text"
                :tabindex="3"
                autocomplete="off"
                placeholder="e.g. Entire cabin"
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
                    :tabindex="4"
                    placeholder="Describe this unit..."
                    v-model="description"
                    rows="12"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                <NumberFormField
                    id="max_guests"
                    label="Max Guests"
                    :tabindex="5"
                    v-model="maxGuests"
                    :min="1"
                    :max="50"
                />

                <NumberFormField
                    id="bedroom_count"
                    label="Total Bedrooms"
                    :tabindex="6"
                    v-model="bedroomCount"
                    :min="0"
                    :max="20"
                />
                
                <NumberFormField
                    id="bathroom_count"
                    label="Total Bathrooms"
                    :tabindex="7"
                    v-model="bathroomCount"
                    :min="0"
                    :max="20"
                />
                
            <div class="col-span-3" v-if="errors.capacity">
                <InputError :message="errors.capacity" />
            </div>
            </div>

            <FormField
                id="view"
                label="View"
                type="text"
                :tabindex="8"
                placeholder="e.g. Lake View"
                v-model="view"
                :error="errors.view"
            />

            <ImageUploader
                :existing-images="existingImages"
                @update:existing-images="existingImages = $event"
                @update:uploaded-media-ids="uploadedMediaIds = $event"
                label="Images"
                name="images"
                :multiple="true"
                :max-files="25"
                :error="errors.images || errors.uploaded_media_ids || errors.existing_images"
                :tabindex="9"
                :disabled="processing"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="10"
                test-id="update-unit-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>