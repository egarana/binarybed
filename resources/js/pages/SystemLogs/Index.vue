<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage()

const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'System Logs',
        href: '/system-logs',
    },
];

const { logs } = defineProps(['logs']);

function clearLogs() {
    router.delete(route('system-logs.clear'));
}
</script>

<template>
    <Head title="System Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template v-if="permissions.includes('clear-system-logs')" #header-tools>
            <div class="ms-auto block py-3">
                <Button variant="outline" @click.prevent="clearLogs">
                    <XCircle /> Clear Logs
                </Button>
            </div>
        </template>

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto">
            <div class="overflow-hidden min-h-full">
                <div class="relative w-full h-full overflow-x-auto">
                    <pre class="bg-zinc-950 text-white p-4 text-[11px] h-full leading-relaxed overflow-auto whitespace-pre-wrap">{{ logs }}</pre>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
