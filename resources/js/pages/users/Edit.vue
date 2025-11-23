<script setup lang="ts">
import users from '@/routes/users';
import { ref } from 'vue';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';
import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
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

const breadcrumbs = useResourceBreadcrumbs({
    resourceName: 'User',
    resourceNamePlural: 'Users',
    indexRoute: users.index.url(),
    action: 'edit',
    actionRoute: users.edit.url(props.user.id),
});

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'user',
    action: 'update',
});

// Form fields
const name = ref(props.user.name || '');
const email = ref(props.user.email || '');
const password = ref('');
</script>

<template>
    <BaseFormPage
        :title="`Edit User: ${user.name}`"
        :breadcrumbs="breadcrumbs"
        :action="users.update.url(user.id)"
        method="put"
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
        </template>
    </BaseFormPage>
</template>