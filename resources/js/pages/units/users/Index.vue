<script setup lang="ts">
import units from '@/routes/units';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useFormNotifications } from '@/composables/useFormNotifications';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
// import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import {
    DialogClose,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
    };
    users?: ComboboxOption[];
}

const props = defineProps<Props>();

const user = ref<ComboboxOption | undefined>(undefined);
const dialogOpen = ref(false);

const config = {
    resourceName: 'User',
    resourceNamePlural: 'Users',
    endpoint: units.users.url([props.unit.tenant_id, props.unit.slug]),
    resourceKey: 'users',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
    ],
    searchFields: ['name', 'email'],
    addButtonLabel: 'Assign user',
    addButtonBehavior: 'dialog' as const,
    breadcrumbs: [
        { title: 'Units', href: units.index.url() },
        { title: 'Manage Users', href: '#' },
    ],
    dialogOpen,
};

const { onSuccess: notifySuccess, onError: notifyError } = useFormNotifications({
    resourceName: 'user',
    action: 'create',
});

// Clear user after dialog closes
const clearUser = () => {
    setTimeout(() => {
        user.value = undefined;
    }, 400);
};

// Custom success handler that clears user after notification and closes dialog
const onSuccess = (payload: any) => {
    notifySuccess(payload);
    user.value = undefined; // Clear user after successful assignment
    dialogOpen.value = false; // Close dialog
};

const onError = notifyError;

</script>

<template>
    <BaseIndexPage title="Manage Users" :config="config">
        <template #dialog-content>
            <DialogHeader>
                <DialogTitle>Assign User</DialogTitle>
                <DialogDescription>
                    Assign a new user to this unit here. Click save when you're done.
                </DialogDescription>
            </DialogHeader>

            <Form
                :action="units.users.attach.url([unit.tenant_id, unit.slug])"
                method="post"
                @success="onSuccess"
                @error="onError"
                class="grid gap-4 py-4"
                v-slot="{ errors, processing }"
            >
                <SearchableSelect
                    mode="single"
                    v-model="user"
                    :options="users"
                    :fetch-url="() => units.users.url([unit.tenant_id, unit.slug])"
                    response-key="users"
                    label="User"
                    placeholder="Select a user"
                    search-placeholder="Search user..."
                    name="user_id"
                    :tabindex="1"
                    :error="errors.user_id"
                    :required="true"
                    :clearable="true"
                    :disable-portal="true"
                    :disabled="processing"
                />

                <DialogFooter>
                    <DialogClose as-child>
                        <Button 
                            variant="outline" 
                            type="button" 
                            @click="clearUser"
                            :disabled="processing"
                        >
                            Cancel
                        </Button>
                    </DialogClose>

                    <SubmitButton
                        :processing="processing"
                        :tabindex="3"
                        test-id="assign-user-button"
                        label="Save"
                        class="!pt-0"
                    />
                </DialogFooter>
            </Form>
        </template>
    </BaseIndexPage>
</template>
