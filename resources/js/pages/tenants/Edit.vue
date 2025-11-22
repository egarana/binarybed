<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useResourceForm } from '@/composables/useResourceForm';
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

const { formRef, onSuccess, onError } = useResourceForm({
    resourceName: 'tenant',
    action: 'update',
});

// Form fields
const name = ref(props.tenant.name || '');
const domain = ref(props.tenant.domain || '');
</script>

<template>
    <Head :title="`Edit Tenant: ${tenant.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <Form
                ref="formRef"
                :action="tenants.update(tenant.id)"
                method="put"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <DisabledFormField
                    label="ID"
                    :value="tenant.id"
                    help-text="Tenant ID cannot be changed after creation"
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
                    test-id="update-tenant-button"
                    label="Save"
                />
            </Form>

        </div>
    </AppLayout>
</template>