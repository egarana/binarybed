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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Units',
        href: units.index.url(),
    },
    {
        title: 'Edit Unit',
        href: units.edit.url([props.unit.tenant_id, props.unit.slug]),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const name = ref(props.unit.name || '');
const { slug } = useAutoSlug(name, { 
    separator: '-', 
    lowercase: true,
    initialValue: props.unit.slug || ''
});

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'update', capitalizeFirst('unit'), payload, {
        successDescription: 'The unit has been updated successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'update', 'unit', payload, {
        errorDescription: 'An unexpected error occurred while updating the unit. Please check your input and try again.',
    });
};
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

                <div class="grid gap-2">
                    <h1 class="disabled-label">Tenant</h1>
                    <div class="disabled-input">
                        {{ unit.tenant_name }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Unit tenant cannot be changed after creation
                    </p>
                </div>

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
                        data-test="update-unit-button"
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
