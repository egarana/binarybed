<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import tenants from '@/routes/tenants';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useResource } from '@/composables/useResource';
import { Table, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Button from '@/components/ui/button/Button.vue';
import { ChevronsUpDown } from 'lucide-vue-next';
import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import { onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenants',
        href: tenants.index(),
    },
];

const { resource: tenantsData, fetchData, isLoading, meta } = useFetcher({
    endpoint: tenants.index(),
    resourceKey: 'tenants',
    preserveState: true,
    debounceMs: 200,
    defaultParams: { per_page: 10 },
});

const { sortState, handleSort } = useSorter({
    fetcher: { fetchData },
    backend: true,
    defaultField: 'name',
});

onMounted(() => {
    fetchData();
});
</script>

<template>
    <Head title="Tenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="overflow-hidden rounded-lg border">
                <div class="relative w-full overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted">
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="handleSort('name')">
                                        Name
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                    </Table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
