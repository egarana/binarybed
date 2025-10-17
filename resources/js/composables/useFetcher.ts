import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
// import type { DebouncedFunc } from 'lodash'; // (opsional) kalau mau ketat

/**
 * Tipe fungsi fetch yang bisa berupa fungsi biasa atau hasil debounce.
 * Dibuat longgar agar tidak tergantung ke tipe DebouncedFunc dari lodash.
 */
type FetchFn = ((params?: Record<string, any>) => void) & {
    cancel?: () => void;
    flush?: () => void;
};

/**
 * Tipe public API yang dikembalikan oleh useFetcher
 */
export interface UseFetcherReturn<T = any> {
    // state utama
    resource: import('vue').Ref<T | null>;
    isLoading: import('vue').Ref<boolean>;
    isError: import('vue').Ref<boolean>;
    errorMessage: import('vue').Ref<string | null>;

    // fungsi utama
    fetchData: FetchFn;
    refresh: () => void;

    // helper
    meta: import('vue').ComputedRef<any>;
    links: import('vue').ComputedRef<any>;
    lastParams: import('vue').Ref<Record<string, any>>;
}

/**
 * @interface FetcherOptions
 * 
 * Opsi konfigurasi universal untuk pengambilan data via Inertia router.get()
 */
interface FetcherOptions<T = any> {
    endpoint: string;                 // contoh: '/vendors' atau route('vendors.index')
    resourceKey: string;              // contoh: 'vendors', 'tenants' (harus sama dengan key di props)
    defaultParams?: Record<string, any>; // parameter query dasar opsional
    preserveScroll?: boolean;         // default true
    preserveState?: boolean;          // default true
    debounceMs?: number;              // waktu debounce dalam ms, jika diisi maka fetch otomatis didebounce
    onSuccess?: (data: T) => void;    // callback jika sukses
    onError?: (errors: Record<string, any>) => void; // callback jika error
}

/**
 * @function useFetcher
 * 
 * Composable powerful untuk fetch data reaktif dari server menggunakan Inertia.js
 * - Mendukung debounce (pencarian dinamis)
 * - Menangani loading, error, meta, links
 * - Aman dari race condition (cancel request lama)
 */
export function useFetcher<T = any>(options: FetcherOptions<T>): UseFetcherReturn<T> {
    const page = usePage();

    /** State utama dan reactive */
    const isLoading = ref(false);
    const isError = ref(false);
    const errorMessage = ref<string | null>(null);
    const resource = ref<T | null>(
        (page.props[options.resourceKey] ?? null) as T
    );
    const lastParams = ref<Record<string, any>>({}); // menyimpan parameter terakhir
    const requestToken = ref<number>(0); // untuk mencegah race condition antar request

    /**
     * Fungsi inti untuk melakukan fetch data dari server
     * - Bisa dipanggil manual atau lewat debounce wrapper
     * - Menangani lifecycle (loading, error, success)
     */
    const doFetch = async (params: Record<string, any> = {}) => {
        const currentToken = Date.now(); // token unik tiap request
        requestToken.value = currentToken;

        isLoading.value = true;
        isError.value = false;
        errorMessage.value = null;

        const query = {
            ...(options.defaultParams ?? {}),
            ...params,
        };

        lastParams.value = query;

        router.get(options.endpoint, query, {
            preserveScroll: options.preserveScroll ?? true,
            preserveState: options.preserveState ?? true,
            only: [options.resourceKey],
            onSuccess: () => {
                // Jika sudah ada request baru, abaikan hasil lama
                if (currentToken !== requestToken.value) return;

                const newData = usePage().props[options.resourceKey] as T;
                resource.value = newData;
                options.onSuccess?.(newData);
            },
            onError: (errors) => {
                if (currentToken !== requestToken.value) return;

                isError.value = true;
                errorMessage.value = 'Terjadi kesalahan saat memuat data.';
                options.onError?.(errors);
            },
            onFinish: () => {
                if (currentToken !== requestToken.value) return;
                isLoading.value = false;
            },
        });
    };

    /**
     * Jika opsi debounce diatur, bungkus doFetch dengan debounce
     * - Contoh: untuk search bar agar tidak request setiap huruf diketik
     */
    const fetchData = (options.debounceMs
        ? debounce(doFetch, options.debounceMs)
        : doFetch) as FetchFn;

    /**
     * Helper untuk memuat ulang data terakhir tanpa kehilangan parameter
     * - Misal digunakan setelah aksi delete/update agar tetap di halaman yang sama
     */
    const refresh = () => {
        fetchData(lastParams.value);
    };

    /** Helper untuk meta dan links pagination */
    const meta = computed(() => (resource.value as any)?.meta ?? null);
    const links = computed(() => (resource.value as any)?.links ?? null);

    return {
        // state utama
        resource,
        isLoading,
        isError,
        errorMessage,

        // fungsi utama
        fetchData,
        refresh,

        // helper
        meta,
        links,
        lastParams,
    };
}
