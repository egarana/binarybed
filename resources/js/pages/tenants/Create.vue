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
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';

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
                <div class="grid gap-2">
                    <Label for="id">ID</Label>
                    <Input
                        id="id"
                        name="id"
                        type="text"
                        :tabindex="1"
                        autocomplete="off"
                        placeholder="e.g. tenantname (lowercase letters and numbers only)"
                        v-model="id"
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
                        v-model="name"
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
                        placeholder="e.g. tenantname.com (no https:// or www, valid domain format)"
                        v-model="domain"
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