<script setup lang="ts">
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';
import { Pencil } from 'lucide-vue-next';
import { Form } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useFormNotifications } from '@/composables/useFormNotifications';
import DisabledFormField from '@/components/DisabledFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface RoleOption {
    value: string;
    label: string;
}

interface Props {
    editUrl: string;
    item: any;
    currentRole: string;
    roleOptions: RoleOption[];
    entityName?: string;
    userDisplayField?: string;
    roleFieldName?: string;
    commissionSplitFieldName?: string;
    getCurrentCommissionSplit?: (item: any) => number;
    title?: string;
    description?: string;
    tooltip?: string;
    icon?: any;
    submitButtonLabel?: string;
    totalCommissionSplit?: number;
}

const props = withDefaults(defineProps<Props>(), {
    entityName: 'user assignment',
    userDisplayField: 'name',
    roleFieldName: 'role',
    commissionSplitFieldName: 'commission_split',
    tooltip: 'Edit assignment',
    submitButtonLabel: 'Save',
    totalCommissionSplit: 0,
});

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const open = ref(false);
const tooltipKey = ref(0);
const tooltipOpen = ref(false);
const preventTooltipOpen = ref(false);
const role = ref(props.currentRole);
const commissionSplit = ref(Number(props.getCurrentCommissionSplit?.(props.item) ?? props.item?.commission_split ?? 0));
const dialogKey = ref(0);

// Check if user is protected (platform owner)
const isProtected = computed(() => props.item?.is_protected === true);

// Calculate remaining commission available globally
// For editing: we show global remaining (100 - total)
// This is consistent with "Assign new user" dialog
const remainingAvailable = computed(() => Math.max(0, 100 - props.totalCommissionSplit));

// Prevent tooltip from opening immediately after dialog closes
watch(tooltipOpen, (newVal) => {
    if (newVal === true && preventTooltipOpen.value) {
        tooltipOpen.value = false;
    }
});

watch(open, (isOpen) => {
    if (!isOpen) {
        // Close tooltip and prevent it from reopening while dialog is closing
        tooltipOpen.value = false;
        preventTooltipOpen.value = true;
        
        // Reset role and commission when dialog closes
        setTimeout(() => {
            role.value = props.currentRole;
            commissionSplit.value = Number(props.getCurrentCommissionSplit?.(props.item) ?? props.item?.commission_split ?? 0);
        }, 400);
        
        // Allow tooltip to open again after dialog animation completes
        setTimeout(() => {
            preventTooltipOpen.value = false;
        }, 500);
    } else {
        // Close tooltip and remount when dialog opens
        tooltipOpen.value = false;
        tooltipKey.value++;
        
        // Update role and commission when dialog opens (in case they changed)
        role.value = props.currentRole;
        commissionSplit.value = Number(props.getCurrentCommissionSplit?.(props.item) ?? props.item?.commission_split ?? 0);
        dialogKey.value++;
    }
});

const { onSuccess, onError } = useFormNotifications({
    resourceName: props.entityName,
    action: 'update',
    successDescription: `${props.entityName.charAt(0).toUpperCase() + props.entityName.slice(1)} has been updated successfully.`,
    errorDescription: `An unexpected error occurred while updating the ${props.entityName}. Please try again.`,
});

const handleSuccess = (payload: any) => {
    onSuccess(payload);
    open.value = false;
    emit('updated');
};
</script>

<template>
    <Dialog v-model:open="open">
        <TooltipProvider>
                <Tooltip 
                    :key="tooltipKey" 
                    v-model:open="tooltipOpen"
                >
                    <TooltipTrigger class="ms-auto" as-child>
                        <DialogTrigger as-child>
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                @click="open = true"
                            >
                                <component :is="props.icon || Pencil" class="w-4 h-4 text-muted-foreground" />
                            </Button>
                        </DialogTrigger>
                    </TooltipTrigger>

                    <TooltipContent>
                        <p>{{ props.tooltip }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

        <DialogContent :key="dialogKey" @escape-key-down.prevent>
            <DialogHeader>
                <DialogTitle>
                    {{ props.title || `Edit ${props.entityName}` }}
                </DialogTitle>

                <DialogDescription>
                    {{ props.description || `Update the ${props.entityName} for this user. Click save when you're done.` }}
                </DialogDescription>
            </DialogHeader>

            <Form
                :action="props.editUrl"
                method="put"
                @success="handleSuccess"
                @error="onError"
                class="grid gap-4 py-4"
                v-slot="{ errors, processing }"
            >
                <DisabledFormField
                    label="User"
                    :value="item[userDisplayField]"
                />

                <!-- Role select - hidden for protected users (platform owner) -->
                <div v-if="!isProtected" class="grid gap-2">
                    <Label>Role</Label>
                    <Select v-model="role" :name="roleFieldName" :disabled="processing">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent :modal="false">
                            <SelectItem 
                                v-for="option in roleOptions" 
                                :key="option.value" 
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors[roleFieldName]" />
                </div>

                <!-- Show role as disabled field for protected users -->
                <DisabledFormField
                    v-else
                    label="Role"
                    value="Platform Owner"
                />

                <!-- Commission split field -->
                <div class="grid gap-2">
                    <Label>Commission Share (%)</Label>
                    <Input 
                        type="number" 
                        v-model="commissionSplit" 
                        :name="commissionSplitFieldName"
                        min="0" 
                        step="0.01"
                        :disabled="processing"
                    />
                    <p class="text-xs text-muted-foreground">Available: {{ remainingAvailable }}% remaining</p>
                    <InputError :message="errors[commissionSplitFieldName]" />
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button 
                            variant="outline" 
                            type="button"
                            :disabled="processing"
                        >
                            Cancel
                        </Button>
                    </DialogClose>

                    <SubmitButton
                        :processing="processing"
                        :tabindex="2"
                        test-id="update-assignment-button"
                        :label="submitButtonLabel"
                        class="!pt-0"
                    />
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>

