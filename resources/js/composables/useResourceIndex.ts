import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import { computed, type Ref } from 'vue';

import type { BreadcrumbItem } from '@/types';
import type { FilterConfig } from '@/components/ResourceTableFilter.vue';

export interface ResourceColumn {
    key: string;
    label: string;
    sortable?: boolean;
    className?: string;
}

export interface CustomAction {
    icon: any;
    tooltip: string;
    url: (item: any) => string;
    variant?: 'ghost' | 'outline' | 'default' | 'secondary' | 'destructive';
    condition?: (item: any) => boolean;
}

export interface UseResourceIndexConfig {
    resourceName: string;
    resourceNamePlural: string;
    endpoint: string;
    resourceKey: string;
    columns: ResourceColumn[];
    searchFields: string[];
    searchPlaceholder?: string;
    addButtonLabel?: string;
    addButtonRoute?: string;
    addButtonBehavior?: 'link' | 'dialog';
    editRoute?: (item: any) => string;
    deleteRoute?: (item: any) => { url: string } | null;
    itemKey?: (item: any) => string;
    customActions?: CustomAction[];
    editAssignmentConfig?: {
        getEditUrl: (item: any) => string;
        getCurrentRole: (item: any) => string;
        roleOptions: Array<{ value: string; label: string }>;
        entityName?: string;
        userDisplayField?: string;
        roleFieldName?: string;
        title?: string;
        description?: string;
        tooltip?: string;
        icon?: any;
        submitButtonLabel?: string;
    };
    showTable?: boolean;
    breadcrumbs: BreadcrumbItem[];
    showSearch?: boolean;
    dialogOpen?: Ref<boolean>;
    deleteIcon?: any;
    deleteActionLabel?: string;
    deleteTitle?: string;
    deleteDescription?: string;
    deleteConfirmLabel?: string;
    filters?: FilterConfig[];
}

export function useResourceIndex(config: UseResourceIndexConfig) {
    // Setup breadcrumbs
    const breadcrumbs = config.breadcrumbs;

    // Setup fetcher
    const { resource, fetchData, refresh, lastParams } = useFetcher({
        endpoint: config.endpoint,
        resourceKey: config.resourceKey,
        preserveScroll: true,
        preserveUrl: false,
    });

    // Setup sorter
    const { sortState, handleSort } = useSorter({
        fetcher: { fetchData, lastParams },
        backend: true,
    });

    // Filter config
    const filterConfig = {
        searchPlaceholder: config.searchPlaceholder || `Search ${config.resourceNamePlural.toLowerCase()}...`,
        searchFields: config.searchFields,
        showAddButton: !!config.addButtonRoute || config.addButtonBehavior === 'dialog',
        addButtonLabel: config.addButtonLabel || `Add ${config.resourceName.toLowerCase()}`,
        addButtonRoute: config.addButtonRoute,
        addButtonBehavior: config.addButtonBehavior,
        showSearch: config.showSearch ?? true,
        dialogOpen: config.dialogOpen,
        filters: config.filters,
    };

    // Table config
    const tableConfig = {
        columns: config.columns,
        editRoute: config.editRoute,
        deleteRoute: config.deleteRoute,
        resourceName: config.resourceName.toLowerCase(),
        itemKey: config.itemKey,
        customActions: config.customActions,
        editAssignmentConfig: config.editAssignmentConfig,
        deleteIcon: config.deleteIcon,
        deleteActionLabel: config.deleteActionLabel,
        deleteTitle: config.deleteTitle,
        deleteDescription: config.deleteDescription,
        deleteConfirmLabel: config.deleteConfirmLabel,
    };

    const showTable = computed(() => {
        if (config.showTable !== undefined) {
            return config.showTable;
        }
        return false;
    });

    return {
        breadcrumbs,
        resource,
        refresh,
        sortState,
        handleSort,
        filterConfig,
        tableConfig,
        showTable,
    };
}