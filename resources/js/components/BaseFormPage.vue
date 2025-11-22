<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

interface Props {
    title: string;
    breadcrumbs: BreadcrumbItem[];
    formRef: any;
    action: string;
    method: 'post' | 'put';
    onSuccess: (payload: any) => void;
    onError: (payload: any) => void;
}

defineProps<Props>();
</script>

<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Form
                ref="formRef"
                :action="action"
                :method="method"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <slot :errors="errors" :processing="processing" />
            </Form>
        </div>
    </AppLayout>
</template>
