<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes'; // 🔧 Ganti dengan route yang sesuai, contoh: { reservations } atau { units }
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// 🔧 Ubah judul breadcrumb & URL sesuai halaman
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard', // 🔧 Ubah: nama halaman
        href: dashboard().url, // 🔧 Ubah: route function
    },
];
</script>

<template>
    <!-- 🔧 Ubah title halaman di tab browser -->
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- 🔧 Konten utama halaman -->
            <div class="text-muted-foreground text-sm">
                Replace this with your page content.
            </div>
        </div>
    </AppLayout>
</template>
