<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import units from '@/routes/units';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useResourceForm } from '@/composables/useResourceForm';
import { useAutoSlug } from '@/composables/useAutoSlug';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'Unit',
    resourceNamePlural: 'Units',
    indexRoute: units.index.url(),
    action: 'edit',
    actionRoute: units.edit.url([props.unit.tenant_id, props.unit.slug]),
});

const { formRef, onSuccess, onError } = useResourceForm({
    resourceName: 'unit',
    action: 'update',
});

// Form fields
const name = ref(props.unit.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.unit.slug || ''
});
</script>

<template>
    <Head :title="`Edit Unit: ${unit.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <Form
                ref="formRef"
                :action="units.update([unit.tenant_id, unit.slug])"
                method="put"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <input type="hidden" name="tenant_id" :value="unit.tenant_id" />

                <DisabledFormField
                    label="Tenant"
                    :value="unit.tenant_name"
                    help-text="Unit tenant cannot be changed after creation"
                />

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
                    test-id="update-unit-button"
                    label="Save"
                />
            </Form>

        </div>
    </AppLayout>
</template>
