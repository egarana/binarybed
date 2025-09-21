<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage()

const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'System Logs',
        href: '/system-logs',
    },
];
</script>

<template>
    <Head title="System Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            
        </div>
    </AppLayout>
</template>
