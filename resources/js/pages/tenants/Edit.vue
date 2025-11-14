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
    <Head :title="`Edit Tenant - ${tenant.name}`" />

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

                <div class="grid gap-2">
                    <h1 class="disabled-label">ID</h1>
                    <div class="disabled-input">
                        {{ tenant.id }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Tenant ID cannot be changed after creation
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        :tabindex="2"
                        :defaultValue="tenant.name"
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
                        :defaultValue="tenant.domain"
                        autocomplete="url"
                        placeholder="e.g. tenantname.com (no https:// or www, valid domain format)"
                    />
                    <InputError :message="errors.domain" />
                </div>

                <div class="mt-auto text-right pt-6">
                    <Button
                        type="submit"
                        tabindex="8"
                        :disabled="processing"
                        data-test="update-tenant-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Update
                    </Button>
                </div>
            </Form>

        </div>
    </AppLayout>
</template>