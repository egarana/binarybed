<script setup lang="ts">
import features from '@/routes/features';
import BaseIndexPage from '@/components/BaseIndexPage.vue';
import { Badge } from '@/components/ui/badge';

const config = {
    resourceName: 'Feature',
    resourceNamePlural: 'Features',
    endpoint: features.index.url(),
    resourceKey: 'features',
    columns: [
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
        <template #cell-category="{ item }">
            <Badge variant="outline" class="capitalize">
                {{ item.category }}
            </Badge>
        </template>
    </BaseIndexPage>
</template>
