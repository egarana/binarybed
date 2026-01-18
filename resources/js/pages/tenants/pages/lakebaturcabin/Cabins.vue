<script setup lang="ts">
import { onMounted } from 'vue';
import Layout from './Layout.vue';
import ResourceCard from '@/components/tenants/default/ResourceCard.vue';
import { useResourceStore, type Resource } from '@/stores/useResourceStore';

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
}

interface ResourceWithRates extends Resource {
    rates: Rate[];
    description?: string;
}

interface Props {
    resources: ResourceWithRates[];
    resourceType: 'units' | 'activities';
    resourceSlug: string;
}

const props = defineProps<Props>();
const resourceStore = useResourceStore();

onMounted(() => {
    if (props.resources?.length > 0) {
        resourceStore.hydrate({ [props.resourceType]: props.resources });
    }
});

</script>

<template>
    <Layout title="Our Cabins">
        <section>
            <div class="px-6 py-8 mx-auto max-w-screen-xl">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ResourceCard 
                        v-for="r in resources" 
                        :key="r.id"
                        :resource="r"
                        :resource-type="resourceType"
                    />
                </div>
            </div>
        </section>
    </Layout>
</template>
