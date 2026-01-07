<script setup lang="ts">
import { computed } from 'vue';
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/helpers/currency';

// Interfaces matching backend data
interface MediaItem {
    id: number;
    uuid: string;
    collection_name: string;
    name: string;
    file_name: string;
    mime_type: string;
    original_url: string;
    preview_url: string | null;
}

interface Feature {
    id: number;
    feature_id: number;
    name: string;
    value: string | null;
    description: string | null;
    icon: string | null;
    pivot: {
        category: string | null;
        order: number | null;
        assigned_at: string | null;
    };
}

interface Rate {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    currency: string;
    price_type: 'flat' | 'person' | 'night' | 'unit' | null;
    is_active: boolean;
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

interface Resource {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    created_at: string;
    updated_at: string;
    features?: Feature[];
    rates?: Rate[];
    media?: MediaItem[];
}

interface Tenant {
    id: string;
    name: string;
    domain: string;
}

interface Props {
    tenant: Tenant;
    resource: Resource;
    resourceType: 'units' | 'activities';
    parentSlug: string;
}

const props = defineProps<Props>();

// Group features by category
const groupedFeatures = computed(() => {
    if (!props.resource.features?.length) return {};
    
    return props.resource.features.reduce((groups, feature) => {
        const category = feature.pivot?.category || 'general';
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(feature);
        return groups;
    }, {} as Record<string, Feature[]>);
});

// Get active rates only
const activeRates = computed(() => {
    return props.resource.rates?.filter(rate => rate.is_active) || [];
});

// Format price type for display
const formatPriceType = (priceType: string | null) => {
    if (!priceType || priceType === 'flat') return '';
    return `/ ${priceType}`;
};

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Get category display name
const getCategoryLabel = (category: string) => {
    const labels: Record<string, string> = {
        amenity: 'Amenities',
        facility: 'Facilities',
        exclusion: 'Not Included',
        inclusion: 'Included',
        suggestion: 'What to Bring',
        general: 'Features',
    };
    return labels[category] || category.charAt(0).toUpperCase() + category.slice(1);
};

// Build parent URL
const parentUrl = computed(() => `/${props.parentSlug}`);
</script>

<template>
    <Layout :title="resource.name">
        <article>
            <!-- Breadcrumb -->
            <nav>
                <Link :href="parentUrl">← Back to {{ parentSlug }}</Link>
            </nav>

            <!-- Header -->
            <header>
                <h1>{{ resource.name }}</h1>
                <p>
                    Type: {{ resourceType === 'units' ? 'Accommodation' : 'Activity' }}
                </p>
            </header>

            <!-- Images Section -->
            <section v-if="resource.media?.length">
                <h2>Images</h2>
                <ul>
                    <li v-for="media in resource.media" :key="media.id">
                        <img 
                            :src="media.original_url" 
                            :alt="media.name"
                            loading="lazy"
                        />
                    </li>
                </ul>
            </section>

            <!-- Description Section -->
            <section v-if="resource.description">
                <h2>Description</h2>
                <p>{{ resource.description }}</p>
            </section>

            <!-- Rates/Pricing Section -->
            <section v-if="activeRates.length">
                <h2>Rates</h2>
                <ul>
                    <li v-for="rate in activeRates" :key="rate.id">
                        <strong>{{ rate.name }}</strong>
                        <span v-if="rate.is_default"> (Default)</span>
                        <br />
                        Price: {{ formatCurrency(rate.price, rate.currency) }} {{ formatPriceType(rate.price_type) }}
                        <p v-if="rate.description">{{ rate.description }}</p>
                    </li>
                </ul>
            </section>

            <!-- Features Sections (grouped by category) -->
            <section v-for="(features, category) in groupedFeatures" :key="category">
                <h2>{{ getCategoryLabel(category) }}</h2>
                <ul>
                    <li v-for="feature in features" :key="feature.id">
                        <span v-if="feature.icon">{{ feature.icon }}</span>
                        {{ feature.name }}
                        <span v-if="feature.description"> - {{ feature.description }}</span>
                    </li>
                </ul>
            </section>

            <!-- Metadata Section -->
            <section>
                <h2>Info</h2>
                <ul>
                    <li>Slug: {{ resource.slug }}</li>
                    <li>Created: {{ formatDate(resource.created_at) }}</li>
                    <li>Last Updated: {{ formatDate(resource.updated_at) }}</li>
                </ul>
            </section>

            <!-- Debug: Raw data -->
            <details>
                <summary>Raw Resource Data (Debug)</summary>
                <pre>{{ resource }}</pre>
            </details>
        </article>
    </Layout>
</template>
