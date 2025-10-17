import { ref, computed } from 'vue';
import type { UseFetcherReturn } from './useFetcher';

/**
 * @interface SorterOptions
 * Konfigurasi dasar sorter agar bisa digunakan lintas komponen
 */
interface SorterOptions {
    defaultField?: string; // kolom default untuk sort awal
    defaultDirection?: 'asc' | 'desc'; // arah default
    fetcher?: Pick<UseFetcherReturn, 'fetchData'>; // integrasi minimal dgn useFetcher (hanya butuh fetchData)
    backend?: boolean; // jika true, maka sorting trigger fetchData()
}

/**
 * @function useSorter
 * Composable universal untuk sorting table.
 * Bisa berdiri sendiri, atau otomatis terhubung ke useFetcher (untuk backend sorting)
 */
export function useSorter(options: SorterOptions = {}) {
    const sortField = ref(options.defaultField || '');
    const sortDirection = ref<'asc' | 'desc'>(options.defaultDirection || 'asc');

    /**
     * Fungsi utama untuk toggle sorting.
     * - Jika kolom sama diklik dua kali → toggle arah sort
     * - Jika kolom berbeda → reset ke ascending
     */
    const handleSort = (field: string) => {
        if (sortField.value === field) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortField.value = field;
            sortDirection.value = 'asc';
        }

        // Jika terhubung ke useFetcher dan backend mode aktif
        if (options.fetcher && options.backend) {
            const directionPrefix = sortDirection.value === 'desc' ? '-' : '';
            options.fetcher.fetchData({ sort: `${directionPrefix}${sortField.value}` });
        }
    };

    /**
     * Computed property agar mudah binding ke template
     * misalnya untuk menampilkan ikon arah panah
     */
    const sortState = computed(() => ({
        field: sortField.value,
        direction: sortDirection.value,
        sortKey: sortDirection.value === 'desc' ? `-${sortField.value}` : sortField.value,
    }));

    /**
     * Helper untuk reset sort ke kondisi awal
     */
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
