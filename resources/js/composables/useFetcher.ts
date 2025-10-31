import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash-es'; // ✅ gunakan versi ES

interface FetcherOptions {
    endpoint: string;
    resourceKey: string | string[];
    params?: Record<string, any>;
    preserveScroll?: boolean;
    preserveState?: boolean;
    preserveUrl?: boolean;
    debounceMs?: number;
}

export function useFetcher<T = any>(options: FetcherOptions) {
    const page = usePage();

    const isLoading = ref(false);
    const errorMessage = ref<string | null>(null);

    const resource = ref<any>(null);

    // init data awal
    const init = () => {
        const props = page.props as Record<string, any>;

        if (Array.isArray(options.resourceKey)) {
            resource.value = {};
            options.resourceKey.forEach(key => {
                resource.value[key] = props[key] ?? null;
            });
        } else {
            resource.value = props[options.resourceKey] ?? null;
        }
    };

    init();

    // raw fetch
    const _fetch = (params: Record<string, any> = {}) => {
        isLoading.value = true;
        errorMessage.value = null;

        const query = {
            ...(options.params ?? {}),
            ...params,
        };

        router.get(options.endpoint, query, {
            preserveScroll: options.preserveScroll ?? true,
            preserveState: options.preserveState ?? true,
            preserveUrl: options.preserveUrl ?? true,
            only: Array.isArray(options.resourceKey)
                ? options.resourceKey
                : [options.resourceKey],

            onSuccess: () => {
                const props = (usePage().props as Record<string, any>);

                if (Array.isArray(options.resourceKey)) {
                    resource.value = {};
                    options.resourceKey.forEach(key => {
                        resource.value[key] = props[key] ?? null;
                    });
                } else {
                    resource.value = props[options.resourceKey] ?? null;
                }
            },

            onFinish: () => {
                isLoading.value = false;
            },

            onError: () => {
                errorMessage.value = 'Terjadi error.';
            },
        });
    };

    // ✅ debounce wrapper (default 300ms)
    const fetchData = debounce(
        (params: Record<string, any> = {}) => _fetch(params),
        options.debounceMs ?? 300
    );

    function refresh(params?: Record<string, any>, options?: any) {
        fetchData(params, options)
    }

    return {
        resource,
        isLoading,
        errorMessage,
        fetchData,
        refresh,
    };
}
