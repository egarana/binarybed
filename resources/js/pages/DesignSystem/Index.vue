<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

// Sample data interfaces
interface MediaItem {
    id: number;
    original_url: string;
}

interface Feature {
    id: number;
    name: string;
}

interface Rate {
    id: number;
    name: string;
    price: number;
    currency: string;
    price_type: string | null;
    is_active: boolean;
}

interface Resource {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    media: MediaItem[];
    features: Feature[];
    rates: Rate[];
}

interface Props {
    sampleResources: Resource[];
}

const props = defineProps<Props>();

// Use samples directly
const samples = computed(() => props.sampleResources);

// Active section
const activeSection = ref('cards');
const sections = [
    { id: 'cards', label: 'Cards' },
    { id: 'headers', label: 'Headers' },
    { id: 'buttons', label: 'Buttons (soon)' },
    { id: 'forms', label: 'Forms (soon)' },
];

// Preview mode
const previewMode = ref<'mobile' | 'tablet' | 'desktop'>('desktop');
const previewWidth = computed(() => {
    switch (previewMode.value) {
        case 'mobile': return 'max-w-sm';
        case 'tablet': return 'max-w-2xl';
        default: return 'max-w-full';
    }
});

// Responsive grid classes based on preview mode
const gridCols3 = computed(() => {
    switch (previewMode.value) {
        case 'mobile': return 'grid-cols-1';
        case 'tablet': return 'grid-cols-2';
        default: return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
    }
});

// Side by side layout classes
const sideBySideLayout = computed(() => {
    switch (previewMode.value) {
        case 'mobile': return 'flex-col';
        case 'tablet': return 'flex-row';
        default: return 'flex-col md:flex-row';
    }
});

const sideBySideImage = computed(() => {
    switch (previewMode.value) {
        case 'mobile': return 'w-full aspect-[4/3]';
        case 'tablet': return 'w-80 aspect-[3/2]';
        default: return 'w-full md:w-80 aspect-[4/3] md:aspect-[3/2]';
    }
});


// Helper functions
const getImage = (r: Resource) => r.media?.[0]?.original_url || null;
const getFeatures = (r: Resource, n = 3) => r.features?.slice(0, n) || [];
const getRemainingFeatures = (r: Resource, shown = 3) => {
    const total = r.features?.length || 0;
    return total > shown ? total - shown : 0;
};
const getLowestPrice = (rates: Rate[]) => {
    const active = rates.filter(r => r.is_active);
    return active.length ? active.reduce((min, r) => r.price < min.price ? r : min, active[0]) : null;
};
const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: currency || 'IDR' }).format(amount);
};
const formatPrice = (type: string | null) => {
    if (!type || type === 'flat') return '';
    const labels: Record<string, string> = { night: '/night', person: '/person', unit: '/unit' };
    return labels[type] || `/${type}`;
};
</script>

<template>
    <Head title="Design System" />
    
    <div class="min-h-screen bg-white flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-50 border-r border-gray-200 p-6 sticky top-0 h-screen overflow-y-auto">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Design System</h1>
            <nav class="space-y-1">
                <button
                    v-for="section in sections"
                    :key="section.id"
                    @click="activeSection = section.id"
                    :class="[
                        'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                        activeSection === section.id 
                            ? 'bg-gray-900 text-white' 
                            : 'text-gray-600 hover:bg-gray-100'
                    ]"
                >
                    {{ section.label }}
                </button>
            </nav>

            <!-- Preview Mode -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-3">Preview</p>
                <div class="flex gap-1">
                    <button
                        @click="previewMode = 'mobile'"
                        :class="['flex-1 py-1.5 text-xs rounded transition-colors', previewMode === 'mobile' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                    >
                        Mobile
                    </button>
                    <button
                        @click="previewMode = 'tablet'"
                        :class="['flex-1 py-1.5 text-xs rounded transition-colors', previewMode === 'tablet' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                    >
                        Tablet
                    </button>
                    <button
                        @click="previewMode = 'desktop'"
                        :class="['flex-1 py-1.5 text-xs rounded transition-colors', previewMode === 'desktop' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                    >
                        Desktop
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div v-if="activeSection === 'cards'" class="space-y-16">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Card Components</h2>
                    <p class="text-gray-500">8 card design variations for showcasing units and activities.</p>
                </div>

                <!-- Preview Container -->
                <div :class="['mx-auto transition-all duration-300', previewWidth]">

                <!-- ===== MOBILE APP STYLE ===== -->
                <section class="space-y-12">
                    <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Mobile App Style</h3>

                    <!-- 1. Airbnb -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">1</span>
                            <h4 class="font-medium text-gray-900">Airbnb</h4>
                        </div>
                        <div :class="['grid gap-5', gridCols3]">
                            <div v-for="r in samples" :key="`1-${r.id}`" class="group cursor-pointer">
                                <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gray-200 mb-3">
                                    <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                </div>
                                <h5 class="font-medium text-gray-900 line-clamp-1 mb-1">{{ r.name }}</h5>
                                <p v-if="r.description" class="text-gray-500 text-base line-clamp-1 mb-1">{{ r.description }}</p>
                                <div v-if="getFeatures(r).length" class="text-gray-400 text-sm mb-2">
                                    {{ getFeatures(r, 2).map(f => f.name).join(' · ') }}
                                    <span v-if="getRemainingFeatures(r, 2)"> · {{ getRemainingFeatures(r, 2) }} more</span>
                                </div>
                                <p v-if="getLowestPrice(r.rates)" class="text-gray-900">
                                    <span class="font-semibold">{{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}</span>
                                    <span class="text-gray-500">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Klook -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">2</span>
                            <h4 class="font-medium text-gray-900">Klook</h4>
                        </div>
                        <div :class="['grid gap-4', gridCols3]">
                            <div v-for="r in samples" :key="`2-${r.id}`" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                                <div class="aspect-[16/10] bg-gray-200">
                                    <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover" />
                                </div>
                                <div class="p-4">
                                    <h5 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ r.name }}</h5>
                                    <p v-if="r.description" class="text-gray-500 text-base line-clamp-2 mb-3">{{ r.description }}</p>
                                    <div v-if="getFeatures(r).length" class="flex flex-wrap items-center gap-1.5 mb-3">
                                        <span v-for="f in getFeatures(r, 2)" :key="f.id" class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">{{ f.name }}</span>
                                        <span v-if="getRemainingFeatures(r, 2)" class="text-xs text-gray-400">{{ getRemainingFeatures(r, 2) }} more</span>
                                    </div>
                                    <p v-if="getLowestPrice(r.rates)" class="text-right">
                                        <span class="text-xs text-gray-400 block">From</span>
                                        <span class="font-bold text-orange-600">{{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Overlay -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">3</span>
                            <h4 class="font-medium text-gray-900">Overlay</h4>
                        </div>
                        <div :class="['grid gap-4', gridCols3]">
                            <div v-for="r in samples" :key="`3-${r.id}`" class="group relative aspect-[4/5] rounded-2xl overflow-hidden bg-gray-200 cursor-pointer">
                                <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                                <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                    <h5 class="font-semibold text-lg mb-1">{{ r.name }}</h5>
                                    <p v-if="r.description" class="text-white/80 text-base line-clamp-2 mb-2">{{ r.description }}</p>
                                    <div v-if="getFeatures(r).length" class="flex flex-wrap gap-1.5 mb-3">
                                        <span v-for="f in getFeatures(r, 2)" :key="f.id" class="bg-white/20 backdrop-blur text-white text-xs px-2 py-0.5 rounded">{{ f.name }}</span>
                                        <span v-if="getRemainingFeatures(r, 2)" class="text-white/60 text-xs">{{ getRemainingFeatures(r, 2) }} more</span>
                                    </div>
                                    <p v-if="getLowestPrice(r.rates)" class="font-bold text-lg">
                                        {{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}
                                        <span class="font-normal text-white/70 text-sm">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Hybrid A -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">4</span>
                            <h4 class="font-medium text-gray-900">Hybrid A</h4>
                        </div>
                        <div :class="['grid gap-5', gridCols3]">
                            <div v-for="r in samples" :key="`4-${r.id}`" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all cursor-pointer">
                                <div class="aspect-[4/3] bg-gray-200 relative overflow-hidden">
                                    <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                        <p v-if="getLowestPrice(r.rates)" class="text-white font-bold text-lg">
                                            {{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}
                                            <span class="font-normal text-white/80 text-sm">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h5 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ r.name }}</h5>
                                    <p v-if="r.description" class="text-gray-500 text-base line-clamp-2 mb-3">{{ r.description }}</p>
                                    <div v-if="getFeatures(r).length" class="flex flex-wrap items-center gap-1.5">
                                        <span v-for="f in getFeatures(r, 3)" :key="f.id" class="bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-full">{{ f.name }}</span>
                                        <span v-if="getRemainingFeatures(r, 3)" class="text-xs text-gray-400">{{ getRemainingFeatures(r, 3) }} more</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Hybrid B -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">5</span>
                            <h4 class="font-medium text-gray-900">Hybrid B</h4>
                        </div>
                        <div :class="['grid gap-4', gridCols3]">
                            <div v-for="r in samples" :key="`5-${r.id}`" class="group cursor-pointer">
                                <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gray-200 mb-3 relative">
                                    <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                    <div v-if="getLowestPrice(r.rates)" class="absolute top-3 right-3 bg-white px-2.5 py-1 rounded-lg shadow">
                                        <span class="font-bold text-gray-900 text-sm">{{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}</span>
                                    </div>
                                </div>
                                <h5 class="font-medium text-gray-900 line-clamp-1 mb-1">{{ r.name }}</h5>
                                <p v-if="r.description" class="text-gray-500 text-base line-clamp-1 mb-2">{{ r.description }}</p>
                                <div v-if="getFeatures(r).length" class="text-xs text-gray-400">
                                    {{ getFeatures(r, 2).map(f => f.name).join(' · ') }}
                                    <span v-if="getRemainingFeatures(r, 2)"> · {{ getRemainingFeatures(r, 2) }} more</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Hybrid D -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">6</span>
                            <h4 class="font-medium text-gray-900">Hybrid D</h4>
                        </div>
                        <div :class="['grid gap-4', gridCols3]">
                            <div v-for="r in samples" :key="`6-${r.id}`" class="group relative aspect-[3/4] rounded-2xl overflow-hidden bg-gray-200 cursor-pointer">
                                <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent" />
                                <div v-if="getLowestPrice(r.rates)" class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1.5 rounded-full">
                                    <span class="font-bold text-gray-900 text-sm">{{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}</span>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 p-5 text-white">
                                    <h5 class="font-bold text-xl mb-2">{{ r.name }}</h5>
                                    <p v-if="r.description" class="text-white/80 text-base line-clamp-2 mb-3">{{ r.description }}</p>
                                    <div v-if="getFeatures(r).length" class="text-white/60 text-sm">
                                        {{ getFeatures(r, 3).map(f => f.name).join(' · ') }}
                                        <span v-if="getRemainingFeatures(r, 3)"> · {{ getRemainingFeatures(r, 3) }} more</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ===== LUXURY MINIMALIST ===== -->
                <section class="space-y-12">
                    <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Luxury Minimalist</h3>

                    <!-- 8. Classic Elegant -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">7</span>
                            <h4 class="font-medium text-gray-900">Classic Elegant</h4>
                        </div>
                        <div :class="['grid gap-10', gridCols3]">
                            <div v-for="r in samples" :key="`7-${r.id}`" class="group cursor-pointer">
                                <div class="aspect-[4/3] rounded-lg overflow-hidden bg-stone-100 mb-5">
                                    <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                                </div>
                                <h5 class="text-lg font-light text-stone-900 mb-2">{{ r.name }}</h5>
                                <p v-if="r.description" class="text-stone-500 font-light text-base leading-relaxed mb-3 line-clamp-2">{{ r.description }}</p>
                                <div v-if="getFeatures(r).length" class="text-stone-400 text-sm mb-3">
                                    {{ getFeatures(r, 2).map(f => f.name).join(' · ') }}
                                    <span v-if="getRemainingFeatures(r, 2)"> · {{ getRemainingFeatures(r, 2) }} more</span>
                                </div>
                                <p v-if="getLowestPrice(r.rates)" class="text-stone-900 font-light text-lg">
                                    {{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}
                                    <span class="text-stone-400 text-sm">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 8. Side by Side -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">8</span>
                            <h4 class="font-medium text-gray-900">Side by Side</h4>
                        </div>
                        <div class="space-y-8">
                            <div v-for="r in samples" :key="`8-${r.id}`" :class="['group flex gap-8 py-6 border-b border-stone-100 hover:bg-stone-50/50 transition-colors rounded-lg cursor-pointer', sideBySideLayout]">
                                <div :class="['rounded-lg overflow-hidden bg-stone-100 flex-shrink-0', sideBySideImage]">
                                    <img v-if="getImage(r)" :src="getImage(r)!" :alt="r.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                </div>
                                <div class="flex-1 flex flex-col justify-center">
                                    <h5 class="text-xl font-light text-stone-900 mb-2">{{ r.name }}</h5>
                                    <p v-if="r.description" class="text-stone-500 font-light text-base leading-relaxed mb-4 line-clamp-2">{{ r.description }}</p>
                                    <div v-if="getFeatures(r).length" class="text-stone-400 text-sm mb-4">
                                        {{ getFeatures(r, 3).map(f => f.name).join(' · ') }}
                                        <span v-if="getRemainingFeatures(r, 3)"> · {{ getRemainingFeatures(r, 3) }} more</span>
                                    </div>
                                    <p v-if="getLowestPrice(r.rates)" class="text-lg text-stone-900 font-light">
                                        {{ formatCurrency(getLowestPrice(r.rates)!.price, getLowestPrice(r.rates)!.currency) }}
                                        <span class="text-stone-400 text-sm">{{ formatPrice(getLowestPrice(r.rates)!.price_type) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                </div> <!-- End Preview Container -->

            </div>

            <!-- ========== HEADERS SECTION ========== -->
            <div v-else-if="activeSection === 'headers'" class="space-y-16">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Page Headers</h2>
                    <p class="text-gray-500">Clean minimalist page header designs with mobile app feel.</p>
                </div>

                <!-- Preview Container -->
                <div :class="['mx-auto transition-all duration-300', previewWidth]">

                    <!-- ===== HYBRID MOBILE STYLE ===== -->
                    <section class="space-y-12">
                        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Hybrid Mobile Style</h3>

                        <!-- 1. Large Title -->
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">1</span>
                                <h4 class="font-medium text-gray-900">Large Title</h4>
                            </div>
                            <p class="text-gray-400 text-sm mb-4">Bold statement title inspired by iOS large titles. Clean and direct.</p>
                            <div class="border rounded-xl overflow-hidden">
                                <div class="bg-white px-5 py-6">
                                    <h1 class="text-3xl font-bold text-gray-900">Cabins</h1>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Title with Subtitle -->
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">2</span>
                                <h4 class="font-medium text-gray-900">Title with Context</h4>
                            </div>
                            <p class="text-gray-400 text-sm mb-4">Adds brand context above the main title. Good for establishing location or category.</p>
                            <div class="border rounded-xl overflow-hidden">
                                <div class="bg-white px-5 py-6">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Lake Batur</p>
                                    <h1 class="text-3xl font-bold text-gray-900">Our Cabins</h1>
                                    <p class="text-gray-500 mt-2">Handpicked lakeside retreats for your perfect getaway</p>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Search Focus -->
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">3</span>
                                <h4 class="font-medium text-gray-900">Search Focus</h4>
                            </div>
                            <p class="text-gray-400 text-sm mb-4">Emphasizes discovery with a prominent search bar. Ideal for extensive catalogs.</p>
                            <div class="border rounded-xl overflow-hidden">
                                <div class="bg-white px-5 py-6">
                                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Discover</h1>
                                    <div class="bg-gray-100 rounded-xl px-4 py-3 flex items-center gap-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <span class="text-gray-400">Search cabins, activities...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Segmented Tabs -->
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">4</span>
                                <h4 class="font-medium text-gray-900">Segmented Tabs</h4>
                            </div>
                            <p class="text-gray-400 text-sm mb-4">iOS-style segmented control for category switching. Clean tab navigation.</p>
                            <div class="border rounded-xl overflow-hidden">
                                <div class="bg-white px-5 py-6">
                                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Browse</h1>
                                    <div class="bg-gray-100 p-1 rounded-xl flex">
                                        <button class="flex-1 py-2.5 text-sm font-medium rounded-lg bg-white shadow-sm text-gray-900">Cabins</button>
                                        <button class="flex-1 py-2.5 text-sm font-medium text-gray-500">Activities</button>
                                        <button class="flex-1 py-2.5 text-sm font-medium text-gray-500">Experiences</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Title with Actions -->
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">5</span>
                                <h4 class="font-medium text-gray-900">Title with Actions</h4>
                            </div>
                            <p class="text-gray-400 text-sm mb-4">Compact header with action buttons. Best for pages with filtering or search needs.</p>
                            <div class="border rounded-xl overflow-hidden">
                                <div class="bg-white px-5 py-4 flex items-center justify-between">
                                    <div>
                                        <h1 class="text-2xl font-bold text-gray-900">Cabins</h1>
                                        <p class="text-gray-400 text-sm">12 available</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                            </svg>
                                        </button>
                                        <button class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Hero Compact -->
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">6</span>
                                <h4 class="font-medium text-gray-900">Hero Compact</h4>
                            </div>
                            <p class="text-gray-400 text-sm mb-4">Visual header with background image. Creates immediate visual impact.</p>
                            <div class="border rounded-xl overflow-hidden">
                                <div class="relative h-36 bg-gray-200">
                                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800" alt="" class="absolute inset-0 w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/20" />
                                    <div class="absolute bottom-0 left-0 right-0 p-5">
                                        <h1 class="text-2xl font-bold text-white">Explore Cabins</h1>
                                        <p class="text-white/70">Find your perfect lakeside escape</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ===== LUXURY MINIMALIST ===== -->
                    <section class="space-y-12 mt-16">
                        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Luxury Minimalist</h3>

                        <!-- 7. Classic Elegant Header (keep as is) -->
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">7</span>
                                <h4 class="font-medium text-gray-900">Classic Elegant</h4>
                            </div>
                            <div class="border border-stone-200 rounded-lg overflow-hidden">
                                <div class="bg-white px-8 py-16 text-center">
                                    <p class="text-stone-400 text-sm uppercase tracking-[0.3em] mb-4">Welcome to</p>
                                    <h1 class="text-3xl font-light text-stone-900 mb-4">Lake Batur <span class="font-serif italic">Cabin</span></h1>
                                    <p class="text-stone-500 font-light max-w-md mx-auto">Experience tranquility in our carefully curated collection of lakeside retreats</p>
                                    <div class="mt-8 flex items-center justify-center gap-8 text-stone-400 text-sm">
                                        <span>Est. 2020</span>
                                        <span class="w-1 h-1 bg-stone-300 rounded-full"></span>
                                        <span>Bali, Indonesia</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 8. Minimal Centered -->
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">8</span>
                                <h4 class="font-medium text-gray-900">Minimal Centered</h4>
                            </div>
                            <div class="border border-stone-100 rounded-lg overflow-hidden">
                                <div class="bg-white px-8 py-12 text-center">
                                    <h1 class="text-2xl font-light text-stone-800 mb-2">Our Cabins</h1>
                                    <p class="text-stone-400 font-light">Carefully selected for your comfort</p>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

            </div>

            <!-- Placeholder for other sections -->
            <div v-else class="text-center py-20 text-gray-400">
                <p class="text-2xl mb-2">🚧</p>
                <p>Coming soon...</p>
            </div>
        </main>
    </div>
</template>
