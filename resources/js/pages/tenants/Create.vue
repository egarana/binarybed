<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { formFor } from '@/helpers/formFor';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';
import { notifyActionResult } from '@/helpers/notifyActionResult';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index(),
    },
    {
        title: 'Create Tenant',
        href: tenants.create(),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: (e) => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'create', 'Tenant', payload, {
        successDescription: 'The database and domain have been set up successfully.',
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
                v-bind="formFor(TenantController.store.post())"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="id">ID</Label>
                    <Input
                        id="id"
                        name="id"
                        type="text"
                        :tabindex="1"
                        autocomplete="off"
                        placeholder="e.g. tenantname"
                    />
                    <InputError :message="errors.id" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        :tabindex="2"
                        autocomplete="organization"
                        placeholder="e.g. Tenant Name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="domain">Domain</Label>
                    <Input
                        id="domain"
                        name="domain"
                        type="text"
                        :tabindex="3"
                        autocomplete="url"
                        placeholder="e.g. tenantname.com (no https:// or www)"
                    />
                    <InputError :message="errors.domain" />
                </div>

                <div class="mt-auto text-right pt-6">
                    <Button
                        type="submit"
                        tabindex="4"
                        :disabled="processing"
                        data-test="create-tenant-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Create
                    </Button>
                </div>
            </Form>

        </div>
    </AppLayout>
</template>
