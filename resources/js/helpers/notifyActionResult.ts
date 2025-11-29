import { toast } from 'vue-sonner';

/**
 * Fungsi universal untuk menampilkan notifikasi hasil aksi (CRUD, sync, login, dll).
 * Bersifat stateless — hanya digunakan untuk menampilkan toast, tanpa menyimpan state.
 */
export function notifyActionResult(
    type: 'success' | 'error',
    action: 'create' | 'update' | 'delete' | 'assign' | 'custom' = 'custom',
    entity: string = 'Record',
    payload?: any,
    options?: {
        customMessage?: string;
        successDescription?: string;
        errorDescription?: string;
    }
) {
    const {
        customMessage,
        successDescription = getDefaultSuccessDescription(action),
        errorDescription = getDefaultErrorDescription(action),
    } = options || {};

    // KASUS BERHASIL
    if (type === 'success') {
        toast(customMessage || getSuccessTitle(entity, action), {
            description: successDescription,
        });
        return;
    }

    // KASUS GAGAL

    // // Deteksi struktur error dari berbagai kemungkinan sumber (Inertia, Axios, dsb)
    const errorsObj =
        (payload && typeof payload === 'object' && !payload.response ? payload : null) ||
        payload?.errors ||
        payload?.response?.data?.errors ||
        payload?.response?.props?.errors;

    // Jika terjadi error validasi → tampilkan toast singkat umum (bukan per-field)
    if (errorsObj && typeof errorsObj === 'object') {
        const fieldCount = Object.keys(errorsObj).length;

        toast(customMessage || getErrorTitle(entity, action, true), {
            description:
                fieldCount === 1
                    ? 'Please review and correct the highlighted field'
                    : `Please review and correct the ${fieldCount} highlighted fields`,
            class: 'toast-destructive',
        });
        return;
    }

    // Jika error sistem / non-validasi → tampilkan toast merah dengan gaya destruktif
    toast(customMessage || getErrorTitle(entity, action), {
        description: payload?.message || errorDescription,
        class: 'toast-destructive',
    });

}

/* ---------- Fungsi bantu internal ---------- */

/**
 * Mendapatkan judul notifikasi untuk aksi yang berhasil.
 */
function getSuccessTitle(entity: string, action: string): string {
    const verbs: Record<string, string> = {
        create: 'created successfully',
        update: 'updated successfully',
        delete: 'deleted successfully',
        assign: 'assigned successfully',
        custom: 'processed successfully',
    };
    return `${entity} ${verbs[action] || 'completed successfully'}`;
}

/**
 * Mendapatkan judul notifikasi untuk aksi yang gagal.
 * Jika parameter isValidation = true, maka dianggap error validasi.
 */
function getErrorTitle(entity: string, action: string, isValidation = false): string {
    const verbs: Record<string, string> = {
        create: 'Error creating',
        update: 'Error updating',
        delete: 'Error deleting',
        assign: 'Error assigning',
        custom: 'Error processing',
    };

    return `${verbs[action] || 'Error processing'} ${entity}`;
}

/**
 * Deskripsi default untuk notifikasi sukses.
 */
function getDefaultSuccessDescription(action: string): string {
    const desc: Record<string, string> = {
        create: 'Record has been successfully added to the system',
        update: 'Changes have been saved successfully',
        delete: 'Record has been removed from the system',
        assign: 'Assignment completed successfully',
        custom: 'Operation completed successfully',
    };
    return desc[action] || 'Action completed successfully';
}

/**
 * Deskripsi default untuk notifikasi gagal.
 */
function getDefaultErrorDescription(action: string): string {
    const desc: Record<string, string> = {
        create: 'There was a problem creating this record. Please try again',
        update: 'There was a problem updating this record. Please try again',
        delete: 'There was a problem deleting this record. Please try again',
        assign: 'There was a problem with the assignment. Please try again',
        custom: 'Something went wrong while processing your request',
    };
    return desc[action] || 'Something went wrong';
}
