<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';

interface Props {
    title: string;
    breadcrumbs: BreadcrumbItem[];
    action: string;
    method: 'post' | 'put';
    onSuccess: (payload: any) => void;
    onError: (payload: any) => void;
    transform?: (data: Record<string, any>) => Record<string, any>;
}

defineProps<Props>();

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Setup keyboard shortcut for form submission (Ctrl+S / Cmd+S)
useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});
</script>

<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Form
                ref="formRef"
                :action="action"
                :method="method"
                :transform="transform"
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