<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import users from '@/routes/users';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useResourceForm } from '@/composables/useResourceForm';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'User',
    resourceNamePlural: 'Users',
    indexRoute: users.index.url(),
    action: 'create',
    actionRoute: users.create.url(),
});

const { formRef, onSuccess, onError } = useResourceForm({
    resourceName: 'user',
    action: 'create',
});

// Form fields
const name = ref('');
const email = ref('');
const password = ref('');
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <Form
                ref="formRef"
                :action="users.store()"
                method="post"
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
                    placeholder="e.g. User Name"
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
                    placeholder="Minimum 6 characters"
                    v-model="password"
                    :error="errors.password"
                />

                <SubmitButton
                    :processing="processing"
                    :tabindex="4"
                    test-id="create-user-button"
                    label="Create"
                />
            </Form>

        </div>
    </AppLayout>
</template>