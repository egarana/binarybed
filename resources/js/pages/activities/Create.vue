<script setup lang="ts">
import activities from '@/routes/activities';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

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

const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});
</script>

<template>
    <BaseFormPage
        title="Create Activity"
        :breadcrumbs="breadcrumbs"
        :action="activities.store.url()"
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
            <SearchableSelect
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
                :tabindex="4"
                :error="errors.features"
                :required="false"
                :draggable="true"
                :disabled="processing"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="5"
                test-id="create-activity-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>