import { ref, computed } from 'vue';
import type { UseFetcherReturn } from './useFetcher';

/**
 * @interface SorterOptions
 * Konfigurasi dasar sorter agar bisa digunakan lintas komponen
 *
 * fetcher:
 * - Bisa berupa objek UseFetcherReturn (full) atau hanya objek minimal { fetchData }.
 * - Jika diberikan full UseFetcherReturn, sorter akan mencoba menggabungkan `lastParams`
 *   ketika memanggil fetchData sehingga parameter lain (mis. page/search) tidak hilang.
 */
interface SorterOptions {
    defaultField?: string; // kolom default untuk sort awal
    defaultDirection?: 'asc' | 'desc'; // arah default
    // menerima either full UseFetcherReturn atau minimal { fetchData }
    fetcher?: Pick<UseFetcherReturn, 'fetchData' | 'lastParams'> | Pick<UseFetcherReturn, 'fetchData'>;
    backend?: boolean; // jika true, maka sorting trigger fetchData()
}

/**
 * @function useSorter
 * Composable universal untuk sorting table.
 * Bisa berdiri sendiri, atau otomatis terhubung ke useFetcher (untuk backend sorting)
 *
 * Penjelasan:
 * - Menyimpan state kolom & arah sort.
 * - handleSort akan toggle arah atau set kolom baru.
 * - Jika opsi backend=true dan fetcher disediakan, maka handleSort akan
 *   memicu fetcher.fetchData(...) dan—jika fetcher menyediakan lastParams—akan
 *   menggabungkan lastParams agar tidak kehilangan parameter query lain.
 */
export function useSorter(options: SorterOptions = {}) {
    // field yang sedang disort dan arah sort
    const sortField = ref(options.defaultField || '');
    const sortDirection = ref<'asc' | 'desc'>(options.defaultDirection || 'asc');

    /**
     * handleSort
     *
     * Toggle / set sort field, lalu bila terhubung ke backend -> panggil fetcher.
     *
     * Strategi pemanggilan fetcher:
     * - Jika fetcher menyediakan lastParams (UseFetcherReturn.full), gabungkan
     *   lastParams.value dengan sort agar parameter lain (page, search, filter) dipertahankan.
     * - Jika hanya fetchData yang ada, panggil dengan object yang hanya berisi sort.
     */
    const handleSort = (field: string) => {
        // jika klik pada kolom yang sama -> toggle arah
        if (sortField.value === field) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            // kolom baru -> set field dan arah default asc
            sortField.value = field;
            sortDirection.value = 'asc';
        }

        // bila terhubung ke backend dan fetcher disediakan, panggil fetcher
        if (options.fetcher && options.backend) {
            // prefix '-' untuk descending (konvensi backend)
            const directionPrefix = sortDirection.value === 'desc' ? '-' : '';

            // cast ke any agar kompatibel dengan kedua bentuk fetcher
            const anyFetcher = options.fetcher as any;

            // Jika fetcher menyediakan lastParams (UseFetcherReturn lengkap),
            // gabungkan lastParams.value dengan sort agar tidak menimpa query lain
            if (anyFetcher.lastParams && typeof anyFetcher.lastParams.value === 'object') {
                // ambil salinan lastParams (jangan mutate langsung)
                const base = { ...(anyFetcher.lastParams.value || {}) };
                // gabungkan sort
                const merged = { ...base, sort: `${directionPrefix}${sortField.value}` };
                // panggil fetchData dengan parameter gabungan
                anyFetcher.fetchData(merged);
            } else {
                // fallback: hanya kirim param sort saja
                anyFetcher.fetchData({ sort: `${directionPrefix}${sortField.value}` });
            }
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
     * resetSort
     * Mengembalikan sorter ke kondisi awal sesuai opsi default.
     */
    const resetSort = () => {
        sortField.value = options.defaultField || '';
        sortDirection.value = options.defaultDirection || 'asc';
    };

    // public API composable
    return {
        sortField,
        sortDirection,
        sortState,
        handleSort,
        resetSort,
    };
}
