<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Layout from './Layout.vue';
import { useResourceStore, type Resource, type Feature, type MediaItem } from '@/stores/useResourceStore';
import { formatCurrency } from '@/helpers/currency';
import ProductGallery from '@/components/tenants/default/ProductGallery.vue';
import ProductHeader from '@/components/tenants/default/ProductHeader.vue';
import UniqueSellingPoints from '@/components/tenants/default/UniqueSellingPoints.vue';
import ProductDescription from '@/components/tenants/default/ProductDescription.vue';
import ProductAmenities from '@/components/tenants/default/ProductAmenities.vue';
import ProductFeaturesSummary from '@/components/tenants/default/ProductFeaturesSummary.vue';
import ProductRules from '@/components/tenants/default/ProductRules.vue';
import ProductBookingBenefits from '@/components/tenants/default/ProductBookingBenefits.vue';
import ProductHost from '@/components/tenants/default/ProductHost.vue';
import ProductLocation from '@/components/tenants/default/ProductLocation.vue';
import ProductPricingCard from '@/components/tenants/default/ProductPricingCard.vue';
import { type TenantData } from '@/stores/useTenantStore';
import { useBenefitsVisibility } from '@/composables/useBenefitsVisibility';

// ============================================
// TYPES - Same pattern as Cabins.vue/Activities.vue
// ============================================
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

// ============================================
// PROPS - Single resource (not array like Cabins.vue)
// Backend sends this from TenantPageController::renderResourceDetailPage()
// ============================================
interface Props {
    resource: ResourceWithRates;
    resourceType: 'units' | 'activities';
    parentSlug: string;
}

const props = defineProps<Props>();
const resourceStore = useResourceStore();
const page = usePage();
const tenant = computed(() => (page.props.tenant as TenantData | undefined));

// ============================================
// COMPUTED HELPERS - Consistent with Cabins.vue/Activities.vue
// ============================================

/** Get all images from resource media */
const images = computed<MediaItem[]>(() => props.resource?.media || []);


/** Get all features */
const features = computed<Feature[]>(() => props.resource?.features || []);

/** Get features by category (using pivot.category) */
const featuresByCategory = computed(() => {
    const grouped: Record<string, Feature[]> = {};
    for (const f of features.value) {
        const category = (f.pivot as { category?: string })?.category || 'general';
        if (!grouped[category]) grouped[category] = [];
        grouped[category].push(f);
    }
    return grouped;
});

/** Get all active rates */
const activeRates = computed<Rate[]>(() => 
    props.resource?.rates?.filter(r => r.is_active) || []
);

/** Get default rate (or lowest price if no default) */
const defaultRate = computed<Rate | null>(() => {
    const rates = activeRates.value;
    if (!rates.length) return null;
    return rates.find(r => r.is_default) || 
           rates.reduce((min, r) => r.price < min.price ? r : min, rates[0]);
});

/** Format price type label */
const formatPriceType = (type: string | null): string => {
    if (!type || type === 'flat') return '';
    const labels: Record<string, string> = { 
        night: '/night', 
        person: '/person', 
        unit: '/unit',
        hourly: '/hour'
    };
    return labels[type] || `/${type}`;
};

// Benefits visibility state for dynamic spacer height
const { isBenefitsHidden } = useBenefitsVisibility();
</script>

<template>
    <Layout :title="resource?.name || 'Product Detail'">
        <section>
            <div class="mx-auto max-w-6xl grid grid-cols-1 relative md:px-6 lg:grid-cols-5 lg:static">
                <!-- Left Column -->
                <div class="lg:col-span-3">
                    <ProductGallery :images="images" />

                    <ProductHeader 
                        :resource="resource" 
                        :resource-type="resourceType" 
                        :tenant-name="tenant?.name"
                    />

                    <UniqueSellingPoints :resource="resource" />

                    <ProductDescription :resource="resource" :resource-type="resourceType" />

                    <ProductAmenities :resource="resource" />

                    <ProductFeaturesSummary :resource="resource" />

                    <ProductLocation :location="resource?.location" />

                    <ProductRules 
                        :resource="resource" 
                        :title="resourceType === 'activities' ? 'Activity rules' : 'House rules'"
                    />

                    <ProductHost :resource="resource" :resource-type="resourceType" />
                </div>
                <!-- Right Column -->

                <div class="fixed bottom-0 w-full lg:relative lg:col-span-2 lg:ps-12">
                    <div class="grid grid-cols-1 gap-2 lg:gap-4 lg:sticky lg:top-8 lg:mb-12">
                        <ProductBookingBenefits :resource="resource" />
                        <ProductPricingCard :resource="resource" />
                    </div>
                </div>
            </div>
        </section>
    </Layout>
    <section :class="['bg-accent lg:hidden', isBenefitsHidden || !resource.book_direct_benefits?.length ? 'h-[74px]' : 'h-32']"></section>
</template>
