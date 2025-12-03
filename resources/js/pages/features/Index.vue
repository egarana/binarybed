<script setup lang="ts">
import features from '@/routes/features';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Badge } from '@/components/ui/badge';
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
        { key: 'category', label: 'Category', sortable: true },
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
    filters: [
        {
            name: 'category',
            label: 'Category',
            placeholder: 'Select categories',
            options: [
                { value: 'amenity', label: 'Amenity' },
                { value: 'equipment', label: 'Equipment' },
                { value: 'exclusion', label: 'Exclusion' },
                { value: 'facility', label: 'Facility' },
                { value: 'inclusion', label: 'Inclusion' },
                { value: 'requirement', label: 'Requirement' },
            ],
        },
    ],
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
        <template #cell-category="{ item }">
            <Badge variant="outline" class="capitalize">
                {{ item.category }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
