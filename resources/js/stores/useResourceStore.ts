import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export interface Resource {
    id: number;
    name: string;
    slug: string;
    created_at: string;
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
    function hydrate(data: Partial<ResourcesData>) {
        if (data.units) units.value = data.units;
        if (data.activities) activities.value = data.activities;
        isHydrated.value = true;
    }

    function hydrateFromInertia() {
        const page = usePage();
        const props = page.props as any;

        // Hydrate from shared data if available
        if (props.sharedResources) {
            hydrate(props.sharedResources);
        }

        // Or hydrate from page-specific resources prop
        if (props.resources && props.resourceType) {
            const resourceType = props.resourceType as 'units' | 'activities';
            hydrate({ [resourceType]: props.resources });
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
        $reset,
    };
});
