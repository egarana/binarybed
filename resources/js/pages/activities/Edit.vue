<script setup lang="ts">
import activities from '@/routes/activities';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader, { type ExistingImage } from '@/components/ImageUploader.vue';
import ActivityHighlightsEditor from '@/components/ActivityHighlightsEditor.vue';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Props {
    activity: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        subtitle?: string;
        description?: string;
        highlights?: any[];
        images?: ExistingImage[];
    };
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

const name = ref(props.activity.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.activity.slug || ''
});

const subtitle = ref(props.activity.subtitle || '');

// Image management
const existingImages = ref<ExistingImage[]>(props.activity.images || []);
const uploadedMediaIds = ref<number[]>([]);
const description = ref(props.activity.description || '');
const highlights = ref(props.activity.highlights || []);

// Transform function to prepare data for submission
function transformFormData(data: Record<string, any>) {
    return {
        ...data,
        existing_images: existingImages.value.map(img => img.id),
        uploaded_media_ids: uploadedMediaIds.value,
        highlights: highlights.value,
        subtitle: subtitle.value,
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

            <FormField
                id="subtitle"
                label="Subtitle"
                type="text"
                :tabindex="3"
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
                    :tabindex="7"
                    placeholder="Describe this activity..."
                    v-model="description"
                    rows="12"
                />
                <InputError :message="errors.description" />
            </div>

            <ActivityHighlightsEditor
                v-model="highlights"
                :error="errors.highlights"
                :tabindex="7"
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
                :tabindex="7"
                :disabled="processing"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="7"
                test-id="update-activity-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>