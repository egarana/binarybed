<script setup lang="ts">
import units from '@/routes/units';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchResourceCombobox, { type ComboboxOption } from '@/components/SearchResourceCombobox.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    tenants?: ComboboxOption[];
}>();

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'Unit',
    resourceNamePlural: 'Units',
    indexRoute: units.index.url(),
    action: 'create',
    actionRoute: units.create.url(),
});

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
</script>

<template>
    <BaseFormPage
        title="Create Unit"
        :breadcrumbs="breadcrumbs"
        :action="units.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <!-- Tenant Selection -->
            <SearchResourceCombobox
                v-model="selectedTenant"
                :initial-items="tenants"
                :fetch-url="() => units.create.url()"
                response-key="tenants"
                label="Tenant"
                placeholder="Select a tenant"
                search-placeholder="Search tenant..."
                hidden-input-name="tenant_id"
                :tabindex="1"
            >
                <template #error>
                    <InputError :message="errors.tenant_id" />
                </template>
            </SearchResourceCombobox>

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

            <SubmitButton
                :processing="processing"
                :tabindex="4"
                test-id="create-unit-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>