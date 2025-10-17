import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Universal composable untuk mengakses props Inertia secara reaktif.
 *
 * @param key - Nama properti yang ingin diambil (misalnya: 'tenants', 'users', 'bookings').
 * @param defaultValue - Nilai default opsional untuk mencegah null/undefined.
 * @returns reactive computed ref ke data resource.
 */
export function useResource<T = unknown>(
    key: string,
    defaultValue?: T
) {
    const page = usePage();

    // computed ref agar data otomatis update saat props berubah
    const resource = computed(() => {
        const props = page.props as Record<string, any>;
        return (props[key] ?? defaultValue) as T;
    });

    return {
        resource,
        props: page.props, // optional: akses penuh ke semua props
        raw: page.props[key] as T | undefined, // tambahan opsional untuk akses langsung
    };
}
