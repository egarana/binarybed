<script setup lang="ts">
import units from '@/routes/units';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Units',
        href: units.index.url(),
    },
    {
        title: 'Attached User',
        href: units.index.url(),
    },
];
</script>

<template>
    <Head title="Attached User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            
        </div>
    </AppLayout>
</template>
