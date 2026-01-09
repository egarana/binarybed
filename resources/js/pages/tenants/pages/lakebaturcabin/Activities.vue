<script setup lang="ts">
import { onMounted } from 'vue';
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';
import { useResourceStore, type Resource, type Feature } from '@/stores/useResourceStore';
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

const getFeatures = (r: ResourceWithRates, n = 3): Feature[] => r.features?.slice(0, n) || [];

const getRemainingFeatures = (r: ResourceWithRates, shown = 3) => {
    const total = r.features?.length || 0;
    return total > shown ? total - shown : 0;
};

const formatPrice = (type: string | null) => {
    if (!type || type === 'flat') return '';
    const labels: Record<string, string> = { night: '/night', person: '/person', unit: '/unit' };
    return labels[type] || `/${type}`;
};
</script>

<template>
    <Layout title="Activities">
        <div class="min-h-screen bg-white">

            <!-- Classic Elegant Header -->
            <header class="border-b border-stone-200">
                <div class="mx-auto max-w-screen-xl px-6 py-16 text-center">
                    <p class="text-stone-400 text-sm uppercase tracking-[0.3em] mb-4">Lake Batur</p>
                    <h1 class="text-3xl font-light text-stone-900 mb-4">Our <span class="font-serif italic">Activities</span></h1>
                    <p class="text-stone-500 font-light max-w-md mx-auto">Discover unforgettable experiences crafted for memorable moments</p>
                </div>
            </header>

            <!-- Classic Elegant Cards Grid -->
            <main class="mx-auto max-w-screen-xl px-6 py-12">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <Link 
                        v-for="r in resources" :key="r.id"
                        :href="`/activities/${r.slug}`"
                        class="group block"
                    >
                        <div class="aspect-[4/3] rounded-lg overflow-hidden bg-stone-100 mb-5">
                            <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                        </div>
                        <h2 class="text-lg font-light text-stone-900 mb-2">{{ r.name }}</h2>
                        <p v-if="r.description" class="text-stone-500 font-light text-base leading-relaxed mb-3 line-clamp-2">{{ r.description }}</p>
                        <div v-if="getFeatures(r).length" class="text-stone-400 text-sm mb-3">
                            {{ getFeatures(r, 2).map(f => f.name).join(' · ') }}
                            <span v-if="getRemainingFeatures(r, 2)"> · {{ getRemainingFeatures(r, 2) }} more</span>
                        </div>
                        <p v-if="getLowestPrice(r.rates)" class="text-stone-900 font-light text-lg">
                            {{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}
                            <span class="text-stone-400 text-sm">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                        </p>
                    </Link>
                </div>
            </main>

        </div>
    </Layout>
</template>
