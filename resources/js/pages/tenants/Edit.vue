<script setup lang="ts">
import tenants from '@/routes/tenants';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

interface Props {
    tenant: {
        id: string;
        name: string;
        domain: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'Tenant',
    resourceNamePlural: 'Tenants',
    indexRoute: tenants.index.url(),
    action: 'edit',
    actionRoute: tenants.edit.url(props.tenant.id),
});

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'tenant',
    action: 'update',
});

// Form fields
const name = ref(props.tenant.name || '');
const domain = ref(props.tenant.domain || '');
</script>

<template>
    <BaseFormPage
        :title="`Edit Tenant: ${tenant.name}`"
        :breadcrumbs="breadcrumbs"
        :action="tenants.update.url(tenant.id)"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="ID"
                :value="tenant.id"
                help-text="Tenant ID cannot be changed after creation"
            />

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="organization"
                placeholder="e.g. Tenant Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="domain"
                label="Domain"
                type="text"
                :tabindex="2"
                autocomplete="url"
                placeholder="e.g. tenantname.com (no https:// or www, valid domain format)"
                v-model="domain"
                :error="errors.domain"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="3"
                test-id="update-tenant-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>