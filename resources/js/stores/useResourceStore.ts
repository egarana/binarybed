import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Media item from Spatie Media Library
 */
export interface MediaItem {
    id: number;
    uuid: string;
    collection_name: string;
    name: string;
    file_name: string;
    mime_type: string;
    original_url: string;
    preview_url: string | null;
}

/**
 * Feature assigned to a resource
 */
export interface Feature {
    id: number;
    feature_id: number;
    name: string;
    value: string | null;
    description: string | null;
    icon: string | null;
    category: string | null;
    pivot: {
        order: number | null;
    };
}

/**
 * Resource (Unit or Activity) - cached data for navigation
 * NOTE: rates are NOT cached - always fetch fresh for booking
 */
/**
 * Base resource fields (always present)
 */
export interface ResourceBase {
    id: number;
    name: string;
    slug: string;
    created_at: string;
}

/**
 * Activity Highlight
 */
export interface Highlight {
    icon: string;
    label: string;
    _id?: string;
}

/**
 * Resource with full details (from page props)
 * NOTE: rates are NOT cached - always fetch fresh for booking
 */
export interface Resource extends ResourceBase {
    updated_at?: string;
    // Unit specific fields (optional as they might not exist on Activities)
    subtitle?: string;
    max_guests?: number;
    bedroom_count?: number;
    bathroom_count?: number;
    view?: string;

    highlights?: Highlight[];
    features?: Feature[];
    media?: MediaItem[];
}

export interface ResourcesData {
    units: Resource[];
    activities: Resource[];
}

export const useResourceStore = defineStore('resources', () => {
    // State
    const units = ref<Resource[]>([]);
    const activities = ref<Resource[]>([]);
    const isHydrated = ref(false);

    // Getters
    const allUnits = computed(() => units.value);
    const allActivities = computed(() => activities.value);
    const totalResources = computed(() => units.value.length + activities.value.length);

    // Actions

    /**
     * Hydrate store with resource data (without rates - rates should be fetched fresh)
     */
    function hydrate(data: Partial<ResourcesData>) {
        if (data.units) {
            // Strip rates if they exist (we don't cache rates)
            units.value = data.units.map(stripRates);
        }
        if (data.activities) {
            // Strip rates if they exist (we don't cache rates)
            activities.value = data.activities.map(stripRates);
        }
        isHydrated.value = true;
    }

    /**
     * Strip rates from resource data before caching
     * Rates should always be fetched fresh for booking
     */
    function stripRates<T extends Resource>(resource: T & { rates?: unknown }): Resource {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        const { rates, ...cached } = resource;
        return cached;
    }

    /**
     * Hydrate from Inertia page props
     */
    function hydrateFromInertia() {
        const page = usePage();
        const props = page.props as Record<string, unknown>;

        // Hydrate from shared data if available
        if (props.sharedResources) {
            hydrate(props.sharedResources as Partial<ResourcesData>);
        }

        // Or hydrate from page-specific resources prop
        if (props.resources && props.resourceType) {
            const resourceType = props.resourceType as 'units' | 'activities';
            hydrate({ [resourceType]: props.resources as Resource[] });
        }
    }

    function getUnitBySlug(slug: string): Resource | undefined {
        return units.value.find(u => u.slug === slug);
    }

    function getActivityBySlug(slug: string): Resource | undefined {
        return activities.value.find(a => a.slug === slug);
    }

    function getResourceBySlug(slug: string, type: 'units' | 'activities'): Resource | undefined {
        return type === 'units' ? getUnitBySlug(slug) : getActivityBySlug(slug);
    }

    function getUnitById(id: number): Resource | undefined {
        return units.value.find(u => u.id === id);
    }

    function getActivityById(id: number): Resource | undefined {
        return activities.value.find(a => a.id === id);
    }

    function getResourceById(id: number, type: 'units' | 'activities'): Resource | undefined {
        return type === 'units' ? getUnitById(id) : getActivityById(id);
    }

    function $reset() {
        units.value = [];
        activities.value = [];
        isHydrated.value = false;
    }

    return {
        // State
        units,
        activities,
        isHydrated,
        // Getters
        allUnits,
        allActivities,
        totalResources,
        // Actions
        hydrate,
        hydrateFromInertia,
        getUnitBySlug,
        getActivityBySlug,
        getResourceBySlug,
        getUnitById,
        getActivityById,
        getResourceById,
        $reset,
    };
});

