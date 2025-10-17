<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import test from '@/routes/test';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Test',
        href: test.index(),
    },
];
</script>

<template>
    <Head title="Test" />

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
