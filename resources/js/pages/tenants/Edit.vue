<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index(),
    },
    {
        title: 'Edit Tenant',
        href: tenants.edit(),
    },
];
</script>

<template>
    <Head title="Edit Tenant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="text-muted-foreground text-sm">
                Replace this with your page content.
            </div>
        </div>
    </AppLayout>
</template>
