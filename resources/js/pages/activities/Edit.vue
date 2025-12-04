<script setup lang="ts">
import activities from '@/routes/activities';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

interface Props {
    activity: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        features?: ComboboxOption[];
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

const name = ref(props.activity.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.activity.slug || ''
});
</script>

<template>
    <BaseFormPage
        :title="`Edit Activity: ${activity.name}`"
        :breadcrumbs="breadcrumbs"
        :action="activities.update.url([activity.tenant_id, activity.slug])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
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

            <!-- Features Selection -->
            <SearchableSelect
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
                :tabindex="3"
                :error="errors.features"
                :required="false"
                :draggable="true"
                :disabled="processing"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="4"
                test-id="update-activity-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>