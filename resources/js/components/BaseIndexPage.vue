<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useResourceIndex, type UseResourceIndexConfig } from '@/composables/useResourceIndex';
import ResourceTable from '@/components/ResourceTable.vue';
import ResourceTableFilter from '@/components/ResourceTableFilter.vue';

interface Props {
    title: string;
    config: UseResourceIndexConfig;
}

const props = defineProps<Props>();

const { breadcrumbs, resource, refresh, sortState, handleSort, filterConfig, tableConfig, showTable } =
    useResourceIndex(props.config);
</script>

<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ResourceTableFilter :refresh="refresh" v-bind="filterConfig">
                <template #dialog-content>
                    <slot name="dialog-content" :refresh="refresh" />
                </template>
            </ResourceTableFilter>
            <ResourceTable
                v-if="showTable"
                :data="resource"
                :sortState="sortState"
                :handleSort="handleSort"
                :refresh="refresh"
                v-bind="tableConfig"
            >
                <template v-for="(_, name) in $slots" v-slot:[name]="slotData">
                    <slot :name="name" v-bind="slotData" />
                </template>
            </ResourceTable>
        </div>
    </AppLayout>
</template>
