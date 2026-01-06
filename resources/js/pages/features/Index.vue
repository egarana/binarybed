<script setup lang="ts">
import features from '@/routes/features';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Minus } from 'lucide-vue-next';

const config = {
    resourceName: 'Feature',
    resourceNamePlural: 'Features',
    endpoint: features.index.url(),
    resourceKey: 'features',
    columns: [
        { key: 'icon', label: 'Icon', sortable: false, headClassName: 'w-[70px] text-center' },
        { key: 'name', label: 'Name', sortable: true, className: 'font-medium' },
        { key: 'value', label: 'Value', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true },
    ],
    searchFields: ['name', 'value', 'description'],
    showTable: true,
    breadcrumbs: [
        { title: 'Features', href: features.index.url() },
    ],
    addButtonRoute: features.create.url(),
    editRoute: (item: any) => features.edit.url(item.id),
    deleteRoute: (item: any) => ({ url: features.destroy.url(item.id) }),
};
</script>

<template>
    <BaseIndexPage title="Features" :config="config">
        <template #cell-icon="{ item }">
            <div v-if="item.icon" class="flex items-center ps-[5px]">
                <span 
                    v-html="item.icon"
                    class="h-5 w-5 [&>svg]:h-5 [&>svg]:w-5 [&>svg]:stroke-current text-muted-foreground"
                />
            </div>
            <div v-else class="flex items-center ps-[5px]">
                <Minus class="h-5 w-5 [&>svg]:h-5 [&>svg]:w-5 [&>svg]:stroke-current text-muted-foreground stroke-1"/>
            </div>
        </template>
    </BaseIndexPage>
</template>
