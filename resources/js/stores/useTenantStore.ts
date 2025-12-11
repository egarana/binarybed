import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export interface TenantData {
    id: string;
    name: string;
    domain: string;
    resource_routes?: Record<string, 'units' | 'activities'>;
    type?: string;
    industry?: string;
    location?: string;
}

export const useTenantStore = defineStore('tenant', () => {
    // State
    const tenant = ref<TenantData | null>(null);
    const isHydrated = ref(false);

    // Getters
    const isTenantContext = computed(() => tenant.value !== null);
    const tenantName = computed(() => tenant.value?.name ?? '');
    const resourceRoutes = computed(() => tenant.value?.resource_routes ?? {});

    // Actions
    function hydrate(data: TenantData | null) {
        tenant.value = data;
        isHydrated.value = true;
    }

    function hydrateFromInertia() {
        const page = usePage();
        const tenantData = (page.props as any).tenant as TenantData | undefined;

        if (tenantData) {
            hydrate(tenantData);
        }
    }

    function getResourceType(routeSlug: string): 'units' | 'activities' | null {
        return resourceRoutes.value[routeSlug] ?? null;
    }

    function $reset() {
        tenant.value = null;
        isHydrated.value = false;
    }

    return {
        // State
        tenant,
        isHydrated,
        // Getters
        isTenantContext,
        tenantName,
        resourceRoutes,
        // Actions
        hydrate,
        hydrateFromInertia,
        getResourceType,
        $reset,
    };
});
