import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { notifyActionResult } from '@/helpers/notifyActionResult';

/**
 * Composable reaktif untuk menangani alur konfirmasi + notifikasi toast.
 * Cocok digunakan dengan komponen seperti shadcn-vue Dialog atau modal konfirmasi lainnya.
 */
export function useNotifyConfirmAction() {
    const isProcessing = ref(false);

    /**
     * Menjalankan aksi yang membutuhkan konfirmasi (hapus, perbarui, sinkronisasi, dll)
     * dengan notifikasi yang terstandarisasi.
     */
    const confirmAndRun = async (
        actionCallback: () => Promise<any>,
        entity: string,
        action: 'delete' | 'update' | 'custom' = 'delete',
        options?: {
            confirmMessage?: string;
            successMessage?: string;
            errorMessage?: string;
        }
    ) => {
        try {
            isProcessing.value = true;

            // Tampilkan pesan konfirmasi (jika ada)
            if (options?.confirmMessage) {
                toast.info(options.confirmMessage);
            }

            // Jalankan callback aksi utama
            const response = await actionCallback();

            // Tampilkan notifikasi sukses
            notifyActionResult('success', action, entity, response, {
                customMessage:
                    options?.successMessage || `${entity} ${getSuccessVerb(action)} successfully.`,
            });
        } catch (error: any) {
            // Tampilkan notifikasi gagal
            notifyActionResult('error', action, entity, error, {
                customMessage:
                    options?.errorMessage || `Failed to ${getErrorVerb(action)} ${entity}.`,
            });
        } finally {
            // Set status kembali ke normal setelah aksi selesai
            isProcessing.value = false;
        }
    };

    return {
        isProcessing,
        confirmAndRun,
    };
}

/* ---------- Fungsi bantu internal ---------- */

/**
 * Mengembalikan kata kerja untuk pesan sukses.
 */
function getSuccessVerb(action: string) {
    const verbs: Record<string, string> = {
        delete: 'deleted',
        update: 'updated',
        custom: 'processed',
    };
    return verbs[action] || 'completed';
}

/**
 * Mengembalikan kata kerja untuk pesan gagal.
 */
function getErrorVerb(action: string) {
    const verbs: Record<string, string> = {
        delete: 'delete',
        update: 'update',
        custom: 'process',
    };
    return verbs[action] || 'execute';
}
