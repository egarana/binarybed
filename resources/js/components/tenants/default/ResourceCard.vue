<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/helpers/currency';
import type { Resource } from '@/stores/useResourceStore';

// Rate interface (consistent with parent pages)
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

// Resource with rates
interface ResourceWithRates extends Resource {
    rates: Rate[];
    description?: string;
}

interface Props {
    resource: ResourceWithRates;
    resourceType: 'units' | 'activities';
}

const props = defineProps<Props>();

// Get the first image from resource media
const resourceImage = computed(() => 
    props.resource.media?.[0]?.original_url || null
);

// Get the lowest active price
const lowestPrice = computed(() => {
    const active = props.resource.rates?.filter(r => r.is_active) || [];
    return active.length 
        ? active.reduce((min, r) => r.price < min.price ? r : min, active[0]) 
        : null;
});

// Format price type label
const formatPriceType = (type: string | null): string => {
    if (!type || type === 'flat') return '';
    const labels: Record<string, string> = { 
        night: '/night', 
        person: '/person', 
        unit: '/unit' 
    };
    return labels[type] || `/${type}`;
};

// Dynamic link based on resource type
const resourceLink = computed(() => {
    const basePath = props.resourceType === 'units' ? '/cabins' : '/activities';
    return `${basePath}/${props.resource.slug}`;
});

// Format details for units (cabins)
const unitDetails = computed(() => {
    const details: string[] = [];
    
    if (props.resource.max_guests) {
        details.push(`${props.resource.max_guests} Guest${props.resource.max_guests > 1 ? 's' : ''}`);
    }
    if (props.resource.bedroom_count) {
        details.push(`${props.resource.bedroom_count} Bedroom${props.resource.bedroom_count > 1 ? 's' : ''}`);
    }
    if (props.resource.bathroom_count) {
        details.push(`${props.resource.bathroom_count} Bathroom${props.resource.bathroom_count > 1 ? 's' : ''}`);
    }
    if (props.resource.view) {
        details.push(props.resource.view);
    }
    
    return details;
});

// Format highlights for activities (limit to 3)
const activityHighlights = computed(() => {
    const highlights = props.resource.highlights || [];
    const limited = highlights.slice(0, 3);
    const remaining = highlights.length > 3 ? highlights.length - 3 : 0;
    
    return {
        items: limited,
        remaining
    };
});
</script>

<template>
    <Link 
        :href="resourceLink"
        class="group block"
    >
        <!-- Image -->
        <div class="aspect-[4/3] rounded-lg overflow-hidden bg-muted-foreground">
            <img 
                v-if="resourceImage" 
                :src="resourceImage" 
                :alt="resource.name" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" 
            />
        </div>

        <!-- Content -->
        <div class="space-y-1.5 mt-6">
            <h2 class="text-lg font-semibold">{{ resource.name }}</h2>
            <p v-if="resource.description" class="line-clamp-2">{{ resource.description }}</p>
            
            <!-- Unit Details (Cabins) -->
            <ul 
                v-if="resourceType === 'units' && unitDetails.length > 0" 
                class="text-muted-foreground text-sm flex flex-wrap gap-x-1.5 gap-y-1"
            >
                <template v-for="(detail, idx) in unitDetails" :key="idx">
                    <li v-if="idx > 0">·</li>
                    <li>{{ detail }}</li>
                </template>
            </ul>

            <!-- Activity Highlights -->
            <ul 
                v-if="resourceType === 'activities' && activityHighlights.items.length > 0" 
                class="text-muted-foreground text-sm flex flex-wrap gap-x-1.5 gap-y-1"
            >
                <template v-for="(highlight, idx) in activityHighlights.items" :key="idx">
                    <li v-if="idx > 0">·</li>
                    <li>{{ highlight.label }}</li>
                </template>
                <template v-if="activityHighlights.remaining > 0">
                    <li>·</li>
                    <li>{{ activityHighlights.remaining }} more</li>
                </template>
            </ul>
        </div>

        <!-- Price -->
        <p v-if="lowestPrice" class="mt-4">
            {{ formatCurrency(lowestPrice.price, lowestPrice.currency) }}<span class="text-muted-foreground text-sm">{{ formatPriceType(lowestPrice.price_type) }}</span>
        </p>
    </Link>
</template>
