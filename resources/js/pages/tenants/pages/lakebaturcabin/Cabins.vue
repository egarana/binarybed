<script setup lang="ts">
import { onMounted } from 'vue';
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';
import { useResourceStore, type Resource } from '@/stores/useResourceStore';
import { formatCurrency } from '@/helpers/currency';

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

const getLowestPrice = (rates: Rate[]) => {
    const active = rates.filter(r => r.is_active);
    return active.length ? active.reduce((min, r) => r.price < min.price ? r : min, active[0]) : null;
};

const getImage = (r: ResourceWithRates) => r.media?.[0]?.original_url || null;



const formatPrice = (type: string | null) => {
    if (!type || type === 'flat') return '';
    const labels: Record<string, string> = { night: '/night', person: '/person', unit: '/unit' };
    return labels[type] || `/${type}`;
};
</script>

<template>
    <Layout title="Our Cabins">
        <section>
            <div class="px-6 py-8 mx-auto max-w-screen-xl">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="r in resources" :key="r.id"
                        :href="`/cabins/${r.slug}`"
                        class="group block"
                    >
                        <div class="aspect-[4/3] rounded-lg overflow-hidden bg-muted-foreground">
                            <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                        </div>
                        <div class="space-y-1.5 mt-6">
                            <h2 class="text-lg font-semibold">{{ r.name }}</h2>
                            <p v-if="r.description" class="line-clamp-2">{{ r.description }}</p>
                            <ul class="text-muted-foreground text-sm flex flex-wrap gap-x-1.5 gap-y-1">
                                <li v-if="r.max_guests">{{ r.max_guests }} Guest{{ r.max_guests > 1 ? 's' : '' }}</li>
                                <li v-if="r.bedroom_count">·</li>
                                <li v-if="r.bedroom_count">{{ r.bedroom_count }} Bedroom{{ r.bedroom_count > 1 ? 's' : '' }}</li>
                                <li v-if="r.bathroom_count">·</li>
                                <li v-if="r.bathroom_count">{{ r.bathroom_count }} Bathroom{{ r.bathroom_count > 1 ? 's' : '' }}</li>
                                <li v-if="r.view">·</li>
                                <li v-if="r.view">{{ r.view }}</li>
                            </ul>
                        </div>
                        <p v-if="getLowestPrice(r.rates)" class="mt-4">
                            {{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}
                            <span class="text-muted-foreground text-sm">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                        </p>
                    </Link>
                </div>
            </div>
        </section>
    </Layout>
</template>
