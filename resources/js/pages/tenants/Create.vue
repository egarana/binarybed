<script setup lang="ts">
import tenants from '@/routes/tenants';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'Tenant',
    resourceNamePlural: 'Tenants',
    indexRoute: tenants.index.url(),
    action: 'create',
    actionRoute: tenants.create.url(),
});

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'tenant',
    action: 'create',
});

// Form fields
const id = ref('');
const name = ref('');
const domain = ref('');
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

            <SubmitButton
                :processing="processing"
                :tabindex="4"
                test-id="create-tenant-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>