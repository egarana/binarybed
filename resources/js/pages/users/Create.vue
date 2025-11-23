<script setup lang="ts">
import users from '@/routes/users';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'User',
    resourceNamePlural: 'Users',
    indexRoute: users.index.url(),
    action: 'create',
    actionRoute: users.create.url(),
});

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'user',
    action: 'create',
});

// Form fields
const name = ref('');
const email = ref('');
const password = ref('');
</script>

<template>
    <BaseFormPage
        title="Create User"
        :breadcrumbs="breadcrumbs"
        :action="users.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
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
        </template>
    </BaseFormPage>
</template>