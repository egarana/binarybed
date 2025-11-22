<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import units from '@/routes/units';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useResourceForm } from '@/composables/useResourceForm';
import { useAutoSlug } from '@/composables/useAutoSlug';
import SearchResourceCombobox, { type ComboboxOption } from '@/components/SearchResourceCombobox.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

const props = defineProps<{
    tenants?: ComboboxOption[];
}>()

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'Unit',
    resourceNamePlural: 'Units',
    indexRoute: units.index.url(),
    action: 'create',
    actionRoute: units.create.url(),
});

const { formRef, onSuccess, onError } = useResourceForm({
    resourceName: 'unit',
    action: 'create',
});

// Form fields
const selectedTenant = ref<ComboboxOption>();
const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});
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

                <FormField
                    id="name"
                    label="Name"
                    type="text"
                    :tabindex="1"
                    autocomplete="organization"
                    placeholder="e.g. Unit Name"
                    v-model="name"
                    :error="errors.name"
                />

                <FormField
                    id="slug"
                    label="Slug"
                    type="text"
                    :tabindex="2"
                    autocomplete="off"
                    placeholder="e.g. unit-name"
                    v-model="slug"
                    :error="errors.slug"
                />

                <SubmitButton
                    :processing="processing"
                    :tabindex="3"
                    test-id="create-unit-button"
                    label="Create"
                />
            </Form>

        </div>
    </AppLayout>
</template>