import { ref, computed } from 'vue';
import type { UseFetcherReturn } from './useFetcher';

interface SorterOptions {
    defaultField?: string;
    defaultDirection?: 'asc' | 'desc';
    fetcher?: Pick<UseFetcherReturn, 'fetchData' | 'lastParams'>;
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
            const sortParam = `${directionPrefix}${sortField.value}`;

            // Merge dengan lastParams untuk mempertahankan filter/params lainnya
            const params = {
                ...(options.fetcher.lastParams?.value || {}),
                sort: sortParam,
            };

            options.fetcher.fetchData(params);
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
