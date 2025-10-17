<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { useNotifyConfirmAction } from '@/composables/useNotifyConfirmAction';

/**
 * Properti yang dapat dikonfigurasi oleh komponen induk.
 */
interface Props {
    title?: string;
    description?: string;
    entity: string;
    action?: 'delete' | 'update' | 'custom';
    open: boolean;
    onClose: () => void;
    onConfirm: () => Promise<any>;
}

const props = defineProps<Props>();
const { isProcessing, confirmAndRun } = useNotifyConfirmAction();

/**
 * Menangani logika ketika tombol "Confirm" ditekan.
 * Akan menjalankan aksi utama dan menutup dialog setelah selesai.
 */
const handleConfirm = async () => {
    await confirmAndRun(props.onConfirm, props.entity, props.action);
    props.onClose();
};
</script>

<template>
    <Dialog :open="props.open" @update:open="props.onClose">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ props.title || 'Confirm Action' }}</DialogTitle>
                <DialogDescription>
                    {{ props.description || 'Are you sure you want to proceed with this action?' }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <!-- Tombol batal -->
                <Button variant="outline" @click="props.onClose">Cancel</Button>

                <!-- Tombol konfirmasi -->
                <Button
                    variant="destructive"
                    :disabled="isProcessing"
                    @click="handleConfirm"
                >
                    {{ isProcessing ? 'Processing...' : 'Confirm' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
