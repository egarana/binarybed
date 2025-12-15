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
import { Trash2 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';

interface Props {
    deleteUrl: string;
    entityName: string;
    deleteData?: Record<string, any>;
    title?: string;
    description?: string;
    tooltip?: string;
    icon?: any;
    confirmText?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'deleted'): void;
}>();

const open = ref(false);
const tooltipKey = ref(0);
const tooltipOpen = ref(false);
const preventTooltipOpen = ref(false);

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
        
        // Allow tooltip to open again after dialog animation completes
        setTimeout(() => {
            preventTooltipOpen.value = false;
        }, 500);
    } else {
        // Close tooltip and remount when dialog opens
        tooltipOpen.value = false;
        tooltipKey.value++;
    }
});

const capitalizedEntityName = capitalizeFirst(props.entityName);

function handleDelete(): void {
    router.delete(props.deleteUrl, {
        data: props.deleteData || {},
        preserveScroll: true,
        preserveState: true,
        preserveUrl: true,
        onSuccess: () => {
            notifyActionResult('success', 'delete', capitalizedEntityName, null);
            open.value = false;
            emit('deleted');
        },
        onError: (errors) => {
            notifyActionResult('error', 'delete', props.entityName, errors, {
                errorDescription: `Failed to delete ${props.entityName}.`,
            });
        },
    });
}
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
                        <Button variant="ghost" size="icon" @click="open = true">
                            <component :is="props.icon || Trash2" class="w-4 h-4 text-muted-foreground" />
                        </Button>
                    </DialogTrigger>
                </TooltipTrigger>

                <TooltipContent>
                    <p>{{ props.tooltip || `Delete ${props.entityName}` }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{ props.title || `Are you sure you want to delete this ${props.entityName}?` }}
                </DialogTitle>

                <DialogDescription>
                    {{
                        props.description ||
                        `If you delete this ${props.entityName}, everything connected to it will also be permanently removed. This action cannot be undone.`
                    }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="ghost">Cancel</Button>
                </DialogClose>

                <Button
                    variant="destructive"
                    @click="handleDelete"
                >
                    {{ props.confirmText || `Delete ${props.entityName}` }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
