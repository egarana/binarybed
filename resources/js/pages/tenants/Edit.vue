<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index.url(),
    },
    {
        title: 'Edit Tenant',
        href: tenants.edit.url(props.tenant.id),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const name = ref(props.tenant.name || '');
const domain = ref(props.tenant.domain || '');

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'update', capitalizeFirst('tenant'), payload, {
        successDescription: 'The tenant has been updated successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'update', 'tenant', payload, {
        errorDescription: 'An unexpected error occurred while updating the tenant. Please check your input and try again.',
    });
};
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