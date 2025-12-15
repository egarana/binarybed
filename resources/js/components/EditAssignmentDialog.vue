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
import { ref, watch } from 'vue';
import { useFormNotifications } from '@/composables/useFormNotifications';
import DisabledFormField from '@/components/DisabledFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
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
    title?: string;
    description?: string;
    tooltip?: string;
    icon?: any;
    submitButtonLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    entityName: 'user assignment',
    userDisplayField: 'name',
    roleFieldName: 'role',
    tooltip: 'Edit assignment',
    submitButtonLabel: 'Save',
});

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const open = ref(false);
const tooltipKey = ref(0);
const tooltipOpen = ref(false);
const preventTooltipOpen = ref(false);
const role = ref(props.currentRole);
const dialogKey = ref(0);

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
        
        // Reset role when dialog closes
        setTimeout(() => {
            role.value = props.currentRole;
        }, 400);
        
        // Allow tooltip to open again after dialog animation completes
        setTimeout(() => {
            preventTooltipOpen.value = false;
        }, 500);
    } else {
        // Close tooltip and remount when dialog opens
        tooltipOpen.value = false;
        tooltipKey.value++;
        
        // Update role when dialog opens (in case currentRole changed)
        role.value = props.currentRole;
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

                <div class="grid gap-2">
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
