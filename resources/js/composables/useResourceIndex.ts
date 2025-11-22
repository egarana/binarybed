import { useFetcher } from '@/composables/useFetcher';
import { useSorter } from '@/composables/useSorter';
import { useResourceBreadcrumbs } from '@/composables/useResourceBreadcrumbs';

export interface ResourceColumn {
    key: string;
    label: string;
    sortable?: boolean;
    className?: string;
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
    editRoute: (item: any) => string;
    deleteRoute: (item: any) => { url: string };
    itemKey?: (item: any) => string;
}

export function useResourceIndex(config: UseResourceIndexConfig) {
    // Setup breadcrumbs
    const breadcrumbs = useResourceBreadcrumbs({
        resourceName: config.resourceName,
        resourceNamePlural: config.resourceNamePlural,
        indexRoute: config.endpoint,
    });

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
        showAddButton: !!config.addButtonRoute,
        addButtonLabel: config.addButtonLabel || `Add ${config.resourceName.toLowerCase()}`,
        addButtonRoute: config.addButtonRoute,
        showSearch: true,
    };

    // Table config
    const tableConfig = {
        columns: config.columns,
        editRoute: config.editRoute,
        deleteRoute: config.deleteRoute,
        resourceName: config.resourceName.toLowerCase(),
        itemKey: config.itemKey,
    };

    return {
        breadcrumbs,
        resource,
        refresh,
        sortState,
        handleSort,
        filterConfig,
        tableConfig,
    };
}
