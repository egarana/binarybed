<script setup lang="ts">
import units from '@/routes/units';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useFormNotifications } from '@/composables/useFormNotifications';
import { useResourceIndex } from '@/composables/useResourceIndex';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import InputError from '@/components/InputError.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import {
    DialogClose,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Unlink, CircleCheckBigIcon } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';

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
const role = ref<string>('partner');
const dialogOpen = ref(false);

const config = {
    resourceName: 'User',
    resourceNamePlural: 'Users',
    endpoint: units.users.url([props.unit.tenant_id, props.unit.slug]),
    resourceKey: 'attachedUsers',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'role', label: 'Role', sortable: true },
        { key: 'assigned_at', label: 'Assigned At', sortable: true },
    ],
    searchFields: ['name', 'email'],
    showTable: true,
    addButtonLabel: 'Assign user',
    addButtonBehavior: 'dialog' as const,
    breadcrumbs: [
        { title: 'Units', href: units.index.url() },
        { title: 'Manage Users', href: '#' },
    ],
    dialogOpen,
    deleteRoute: (item: any) => ({ 
        url: units.users.detach.url([props.unit.tenant_id, props.unit.slug, item.global_id])
    }),
    deleteIcon: Unlink,
    deleteActionLabel: 'Remove user',
    deleteTitle: 'Remove user from this unit?',
    deleteDescription: 'If you remove this user, they will no longer be associated with this unit. You can add them back anytime.',
    deleteConfirmLabel: 'Remove user',
    editAssignmentConfig: {
        getEditUrl: (item: any) => units.users.update.url([props.unit.tenant_id, props.unit.slug, item.global_id]),
        getCurrentRole: (item: any) => item.role,
        roleOptions: [
            { value: 'partner', label: 'Partner' },
            { value: 'referrer', label: 'Referrer' },
        ],
        entityName: 'user assignment',
        userDisplayField: 'name',
        roleFieldName: 'role',
        title: 'Edit user assignment',
        description: 'Update the user assignment for this user. Click save when you\'re done.',
        tooltip: 'Edit assignment',
    },
};

// Get refresh function from the composable
const { refresh } = useResourceIndex(config);

const { onSuccess: notifySuccess, onError: notifyError } = useFormNotifications({
    resourceName: 'user',
    action: 'assign',
    successDescription: 'User has been assigned to the unit successfully.',
    errorDescription: 'An unexpected error occurred while assigning the user to the unit. Please try again.',
});

// Clear user after dialog closes
const clearUser = () => {
    setTimeout(() => {
        user.value = undefined;
        role.value = 'partner';
    }, 400);
};


</script>

<template>
    <BaseIndexPage title="Manage Users" :config="config">
        <template #dialog-content="{ refresh }">
            <DialogHeader>
                <DialogTitle>Assign user to unit</DialogTitle>
                <DialogDescription>
                    Assign a new user to this unit here. Click save when you're done.
                </DialogDescription>
            </DialogHeader>

            <Form
                :action="units.users.attach.url([unit.tenant_id, unit.slug])"
                method="post"
                @success="(payload) => { notifySuccess(payload); user = undefined; role = 'partner'; dialogOpen = false; refresh(); }"
                @error="notifyError"
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

                <div class="grid gap-2">
                    <Label>Role</Label>
                    <Select v-model="role" name="role" :disabled="processing">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="partner">Partner</SelectItem>
                            <SelectItem value="referrer">Referrer</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.role" />
                </div>

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

        <template #cell-role="{ item }">
            <Badge 
                :variant="item.role === 'partner' ? 'secondary' : 'outline'" 
                class="capitalize"
            >
                <CircleCheckBigIcon v-if="item.role === 'partner'" />
                {{ item.role }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>

