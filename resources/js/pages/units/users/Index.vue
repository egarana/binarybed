<script setup lang="ts">
import units from '@/routes/units';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Form, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, reactive } from 'vue';
import { useFormNotifications } from '@/composables/useFormNotifications';
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
import { Input } from '@/components/ui/input';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
    };
    users?: ComboboxOption[];
    totalCommissionSplit?: number;
}

const props = defineProps<Props>();

const remainingAllocation = computed(() => Math.max(0, 100 - (props.totalCommissionSplit ?? 0)));

const user = ref<ComboboxOption | undefined>(undefined);
const role = ref<string>('partner');
const commissionSplit = ref<number>(0);
const dialogOpen = ref(false);

// Reset commission split to remaining allocation when dialog opens
watch(dialogOpen, (isOpen) => {
    if (isOpen) {
        commissionSplit.value = remainingAllocation.value;
    }
});

// Get attached user IDs to exclude from dropdown
const attachedUserIds = computed(() => {
    const pageProps = usePage().props as any;
    const attached = pageProps.attachedUsers?.data ?? pageProps.attachedUsers ?? [];
    return attached.map((u: any) => u.global_id);
});

const editAssignmentConfig = reactive({
    getEditUrl: (item: any) => units.users.update.url([props.unit.tenant_id, props.unit.slug, item.global_id]),
    getCurrentRole: (item: any) => item.role,
    getCurrentCommissionSplit: (item: any) => item.commission_split,
    roleOptions: [
        { value: 'partner', label: 'Partner' },
        { value: 'referrer', label: 'Referrer' },
    ],
    entityName: 'user assignment',
    userDisplayField: 'name',
    roleFieldName: 'role',
    commissionSplitFieldName: 'commission_split',
    title: 'Edit user assignment',
    description: 'Update the user assignment for this user. Click save when you\'re done.',
    tooltip: 'Edit assignment',
    totalCommissionSplit: props.totalCommissionSplit ?? 0,
});

watch(() => props.totalCommissionSplit, (newVal) => {
    editAssignmentConfig.totalCommissionSplit = newVal ?? 0;
}, { immediate: true });

const config = {
    resourceName: 'User',
    resourceNamePlural: 'Users',
    endpoint: units.users.url([props.unit.tenant_id, props.unit.slug]),
    resourceKey: 'attachedUsers',
    columns: [
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'role', label: 'Role', sortable: true },
        { key: 'commission_split', label: 'Commission (%)', sortable: true },
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
    deleteRoute: (item: any) => item.is_protected ? null : ({ 
        url: units.users.detach.url([props.unit.tenant_id, props.unit.slug, item.global_id])
    }),
    deleteIcon: Unlink,
    deleteActionLabel: 'Remove user',
    deleteTitle: 'Remove user from this unit?',
    deleteDescription: 'If you remove this user, they will no longer be associated with this unit. You can add them back anytime.',
    deleteConfirmLabel: 'Remove user',
    editAssignmentConfig,
};

// useResourceIndex is called internally by BaseIndexPage
// The refresh function is provided via the slot props

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
        commissionSplit.value = remainingAllocation.value;
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
                @success="(payload) => { notifySuccess(payload); user = undefined; role = 'partner'; commissionSplit = remainingAllocation; dialogOpen = false; refresh(); }"
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
                    :exclude-values="attachedUserIds"
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

                <div class="grid gap-2">
                    <Label>Commission Share (%)</Label>
                    <Input 
                        type="number" 
                        v-model="commissionSplit" 
                        name="commission_split"
                        min="0" 
                        step="0.01"
                        :disabled="processing"
                    />
                    <p class="text-xs text-muted-foreground">Available: {{ remainingAllocation }}% remaining</p>
                    <InputError :message="errors.commission_split" />
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

        <template #cell-commission_split="{ item }">
            <span class="font-medium">{{ Number(item.commission_split) % 1 === 0 ? Math.round(item.commission_split) : item.commission_split }}%</span>
        </template>
    </BaseIndexPage>
</template>

