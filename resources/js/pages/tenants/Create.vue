<script setup lang="ts">
import tenants from '@/routes/tenants';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import FormField from '@/components/FormField.vue';
import ResourceRoutesEditor from '@/components/ResourceRoutesEditor.vue';
import SubmitButton from '@/components/SubmitButton.vue';

type ResourceType = 'units' | 'activities';

const breadcrumbs = [
    { title: 'Tenants', href: tenants.index.url() },
    { title: 'Create Tenant', href: tenants.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'tenant',
    action: 'create',
});

// Form fields
const id = ref('');
const name = ref('');
const domain = ref('');
const resourceRoutes = ref<Record<string, ResourceType>>({});

// Serialize resourceRoutes for form submission
const resourceRoutesJson = computed(() => JSON.stringify(resourceRoutes.value));
</script>

<template>
    <BaseFormPage
        title="Create Tenant"
        :breadcrumbs="breadcrumbs"
        :action="tenants.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <FormField
                id="id"
                label="ID"
                type="text"
                :tabindex="1"
                autocomplete="off"
                placeholder="e.g. tenantname (lowercase letters and numbers only)"
                v-model="id"
                :error="errors.id"
            />

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="2"
                autocomplete="organization"
                placeholder="e.g. Tenant Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="domain"
                label="Domain"
                type="text"
                :tabindex="3"
                autocomplete="url"
                placeholder="e.g. tenantname.com (no https:// or www, valid domain format)"
                v-model="domain"
                :error="errors.domain"
            />

            <ResourceRoutesEditor
                v-model="resourceRoutes"
                :error="errors.resource_routes"
            />

            <!-- Hidden input to send resource_routes as JSON -->
            <input type="hidden" name="resource_routes" :value="resourceRoutesJson" />

            <SubmitButton
                :processing="processing"
                :tabindex="4"
                test-id="create-tenant-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>