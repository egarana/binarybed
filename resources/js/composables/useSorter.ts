import { ref, computed } from 'vue';
import type { UseFetcherReturn } from './useFetcher';

interface SorterOptions {
    defaultField?: string;
    defaultDirection?: 'asc' | 'desc';
    fetcher?: Pick<UseFetcherReturn, 'fetchData' | 'lastParams'> | Pick<UseFetcherReturn, 'fetchData'>;
    backend?: boolean;
}

export function useSorter(options: SorterOptions = {}) {
    const sortField = ref(options.defaultField || '');
    const sortDirection = ref<'asc' | 'desc'>(options.defaultDirection || 'asc');

    const handleSort = (field: string) => {
        if (sortField.value === field) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortField.value = field;
            sortDirection.value = 'asc';
        }

        if (options.fetcher && options.backend) {
            const directionPrefix = sortDirection.value === 'desc' ? '-' : '';
            const anyFetcher = options.fetcher as any;

            if (anyFetcher.lastParams && typeof anyFetcher.lastParams.value === 'object') {
                const base = { ...(anyFetcher.lastParams.value || {}) };
                const merged = { ...base, sort: `${directionPrefix}${sortField.value}` };
                anyFetcher.fetchData(merged);
            } else {
                anyFetcher.fetchData({ sort: `${directionPrefix}${sortField.value}` });
            }
        }
    };

    const sortState = computed(() => ({
        field: sortField.value,
        direction: sortDirection.value,
        sortKey: sortDirection.value === 'desc' ? `-${sortField.value}` : sortField.value,
    }));

    const resetSort = () => {
        sortField.value = options.defaultField || '';
        sortDirection.value = options.defaultDirection || 'asc';
    };

    return {
        sortField,
        sortDirection,
        sortState,
        handleSort,
        resetSort,
    };
}
