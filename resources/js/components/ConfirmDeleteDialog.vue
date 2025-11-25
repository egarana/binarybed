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
import { ref } from 'vue';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';

interface Props {
    deleteUrl: string;
    entityName: string;
    deleteData?: Record<string, any>;
    title?: string;
    description?: string;
    tooltip?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'deleted'): void;
}>();

const open = ref(false);

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
            <Tooltip>
                <TooltipTrigger>
                    <DialogTrigger as-child>
                        <Button variant="outline" size="icon" @click="open = true">
                            <Trash2 class="w-4 h-4 text-muted-foreground" />
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
                    <Button variant="outline">Cancel</Button>
                </DialogClose>

                <Button
                    variant="destructive"
                    @click="handleDelete"
                >
                    Delete {{ props.entityName }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
