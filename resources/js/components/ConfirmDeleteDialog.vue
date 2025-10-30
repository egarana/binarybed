<script setup lang="ts">
/**
 * Komponen universal untuk menampilkan dialog konfirmasi penghapusan entity apa pun
 * (Tenant, Property, User, Reservation, Rate, dsb).
 *
 * Tujuan:
 * - Reusable di seluruh aplikasi (universal).
 * - Menutup dialog otomatis setelah aksi sukses.
 * - Memicu event "deleted" ke parent agar data di-refresh.
 * - Terintegrasi dengan shadcn/ui + Inertia.js + helper notifikasi global.
 */

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

/**
 * Props untuk komponen ini.
 * deleteUrl  → URL endpoint DELETE dari resource.
 * entityName → Nama entity (mis. "Tenant", "User") untuk notifikasi & teks dialog.
 * title, description, tooltip bersifat opsional untuk kustomisasi tampilan.
 */
interface Props {
    deleteUrl: string;
    entityName: string;
    title?: string;
    description?: string;
    tooltip?: string;
}

const props = defineProps<Props>();

/**
 * Event emitter agar komponen ini bisa berkomunikasi dengan parent.
 * Event: "deleted" → dipanggil setelah aksi delete sukses.
 */
const emit = defineEmits<{
    (e: 'deleted'): void;
}>();

/**
 * State reaktif untuk mengontrol apakah dialog sedang terbuka.
 * Menggunakan binding v-model agar bisa dikontrol secara manual (misalnya ditutup otomatis).
 */
const open = ref(false);

/**
 * Fungsi utama handleDelete()
 * - Menjalankan request DELETE via Inertia router.
 * - Menampilkan notifikasi hasil.
 * - Menutup dialog & memicu event deleted jika sukses.
 */

const entityName = capitalizeFirst(props.entityName);

function handleDelete(): void {
    router.delete(props.deleteUrl, {
        preserveScroll: true, // menjaga posisi scroll halaman
        onSuccess: () => {
            // Tampilkan notifikasi sukses
            notifyActionResult('success', 'delete', entityName, null);

            // Tutup dialog
            open.value = false;

            // Emit event ke parent agar bisa memanggil refresh()
            emit('deleted');
        },
        onError: (errors) => {
            // Tampilkan notifikasi error
            notifyActionResult('error', 'delete', props.entityName, errors, {
                errorDescription: `Failed to delete ${props.entityName}.`,
            });
        },
    });
}
</script>

<template>
    <!--
        Komponen dialog konfirmasi universal.
        Menggunakan shadcn/ui untuk konsistensi desain.
        Binding open ke state agar bisa menutup otomatis setelah aksi sukses.
    -->
    <Dialog v-model:open="open">
        <TooltipProvider>
            <Tooltip>
                <!-- Trigger utama untuk membuka dialog -->
                <TooltipTrigger>
                    <DialogTrigger as-child>
                        <Button variant="ghost" size="icon" @click="open = true">
                            <!-- Ikon Trash -->
                            <Trash2 class="w-4 h-4 text-muted-foreground" />
                        </Button>
                    </DialogTrigger>
                </TooltipTrigger>

                <!-- Tooltip teks saat hover -->
                <TooltipContent>
                    <p>{{ props.tooltip || `Delete ${props.entityName}` }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <!-- Konten utama dialog -->
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
                <!-- Tombol Cancel untuk menutup dialog tanpa aksi -->
                <DialogClose as-child>
                    <Button variant="outline">Cancel</Button>
                </DialogClose>

                <!-- Tombol Delete untuk menjalankan aksi handleDelete -->
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
