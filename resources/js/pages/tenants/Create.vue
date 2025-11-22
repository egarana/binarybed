<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index.url(),
    },
    {
        title: 'Create Tenant',
        href: tenants.create.url(),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const id = ref('');
const name = ref('');
const domain = ref('');

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'create', capitalizeFirst('tenant'), payload, {
        successDescription: 'The tenant has been created successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'create', 'tenant', payload, {
        errorDescription: 'An unexpected error occurred while creating the tenant. Please check your input and try again.',
    });
};
</script>

<template>
    <Head title="Create Tenant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <Form
                ref="formRef"
                :action="tenants.store()"
                method="post"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
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
            </Form>

        </div>
    </AppLayout>
</template>