<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import users from '@/routes/users';
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
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: users.index.url(),
    },
    {
        title: 'Edit User',
        href: users.edit.url(props.user.id),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const name = ref(props.user.name || '');
const email = ref(props.user.email || '');
const password = ref('');

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'update', capitalizeFirst('user'), payload, {
        successDescription: 'The user has been updated successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'update', 'user', payload, {
        errorDescription: 'An unexpected error occurred while updating the user. Please check your input and try again.',
    });
};
</script>

<template>
    <Head :title="`Edit User: ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            
            <Form
                ref="formRef"
                :action="users.update(user.id)"
                method="put"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <FormField
                    id="name"
                    label="Name"
                    type="text"
                    :tabindex="1"
                    autocomplete="name"
                    placeholder="e.g. John Doe"
                    v-model="name"
                    :error="errors.name"
                />

                <FormField
                    id="email"
                    label="Email"
                    type="email"
                    :tabindex="2"
                    autocomplete="email"
                    placeholder="e.g. john@example.com"
                    v-model="email"
                    :error="errors.email"
                />

                <FormField
                    id="password"
                    label="Password"
                    type="password"
                    :tabindex="3"
                    autocomplete="new-password"
                    placeholder="Leave blank to keep current password"
                    v-model="password"
                    :error="errors.password"
                    help-text="Only fill this field if you want to change the password (minimum 6 characters)"
                />

                <SubmitButton
                    :processing="processing"
                    :tabindex="4"
                    test-id="update-user-button"
                    label="Save"
                />
            </Form>

        </div>
    </AppLayout>
</template>
