<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import units from '@/routes/units';
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
import { useAutoSlug } from '@/composables/useAutoSlug';
import SearchResourceCombobox, { type ComboboxOption } from '@/components/SearchResourceCombobox.vue';

const props = defineProps<{
    tenants?: ComboboxOption[];
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Units',
        href: units.index.url(),
    },
    {
        title: 'Create Unit',
        href: units.create.url(),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const selectedTenant = ref<ComboboxOption>();
const name = ref('');
const { slug } = useAutoSlug(name, { 
    separator: '-', 
    lowercase: true 
});

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'create', capitalizeFirst('unit'), payload, {
        successDescription: 'The unit has been created successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'create', 'unit', payload, {
        errorDescription: 'An unexpected error occurred while creating the unit. Please check your input and try again.',
    });
};
</script>

<template>
    <Head title="Create Unit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <Form
                ref="formRef"
                :action="units.store()"
                method="post"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
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
                >
                    <template #error>
                    <InputError :message="errors.tenant_id" />
                    </template>
                </SearchResourceCombobox>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        :tabindex="1"
                        autocomplete="organization"
                        placeholder="e.g. Unit Name"
                        v-model="name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="slug">Slug</Label>
                    <Input
                        id="slug"
                        name="slug"
                        type="text"
                        :tabindex="2"
                        autocomplete="off"
                        placeholder="e.g. unit-name"
                        v-model="slug"
                    />
                    <InputError :message="errors.slug" />
                </div>

                <div class="mt-auto text-right pt-6">
                    <Button
                        type="submit"
                        tabindex="3"
                        :disabled="processing"
                        data-test="create-unit-button"
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