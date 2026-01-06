<script setup lang="ts">
import { onMounted } from 'vue';
import Layout from './Layout.vue';
import { useResourceStore, type Resource } from '@/stores/useResourceStore';

// Rate interface - NOT cached, always fetch fresh
interface Rate {
    id: number;
    rateable_type: string;
    rateable_id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    currency: string;
    price_type: string | null;
    is_active: boolean;
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

// Full resource with rates (from Inertia props)
interface ResourceWithRates extends Resource {
    rates: Rate[];
}

interface Props {
    resources: ResourceWithRates[];
    resourceType: 'units' | 'activities';
    resourceSlug: string;
}

const props = defineProps<Props>();
const resourceStore = useResourceStore();

// Hydrate store on mount (rates will be stripped automatically)
onMounted(() => {
    if (props.resources && props.resources.length > 0) {
        resourceStore.hydrate({ [props.resourceType]: props.resources });
    }
});
</script>

<template>
    <Layout title="Cabins">
        <pre class="text-xs">
            {{ resources }}
        </pre>
    </Layout>
</template>
