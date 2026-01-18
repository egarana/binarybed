<script setup lang="ts">
import { computed, ref } from 'vue';
import { useMediaQuery } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { type Resource } from '@/stores/useResourceStore';
import { useBenefitsVisibility } from '@/composables/useBenefitsVisibility';
import {
    Drawer,
    DrawerContent,
    DrawerHeader,
    DrawerTitle,
    DrawerDescription,
    DrawerTrigger,
} from '@/components/ui/drawer';
import PricingBreakdownContent from '@/components/tenants/default/PricingBreakdownContent.vue';
import PricingInfo from '@/components/tenants/default/PricingInfo.vue';

interface Props {
    resource: Resource;
}

const props = defineProps<Props>();

// Shared state for positioning logic
const { isBenefitsHidden } = useBenefitsVisibility();

// Check if benefits exist for positioning calculation
const hasBenefits = computed(() => (props.resource.book_direct_benefits?.length || 0) > 0);

// State for pricing drawer (mobile only)
const isPricingOpen = ref(false);

// Check if desktop
const isDesktop = useMediaQuery('(min-width: 1024px)');
</script>

<template>
    <div class="bg-background border-t flex items-center justify-between px-6 py-4 lg:block lg:p-6 lg:border lg:rounded-md">
        <div>
            <PricingInfo />
        </div>
        <div>
            <Button size="lg" class="lg:w-full">Book Now</Button>
        </div>
    </div>
    <!-- <div 
        class="fixed bottom-0 w-full bg-background px-6 border-t lg:sticky lg:bottom-auto lg:border lg:rounded-lg lg:p-6 lg:mb-12" 
        :class="[
            isBenefitsHidden || !hasBenefits ? 'lg:top-8 lg:mt-0' : 'lg:top-24 lg:mt-5'
        ]"
    >
        <div class="flex items-center justify-between py-4 lg:block lg:py-0">
            <Drawer v-if="!isDesktop" v-model:open="isPricingOpen">
                <DrawerTrigger as-child>
                    <button class="text-left hover:opacity-80 transition-opacity underline underline-offset-2 hover:no-underline">
                        <PricingInfo />
                    </button>
                </DrawerTrigger>

                <DrawerContent class="max-h-[80vh] px-0 *:px-6">   
                    <DrawerHeader class="p-6">
                        <DrawerTitle class="text-lg">Price Breakdown</DrawerTitle>
                        <DrawerDescription class="text-base">Detailed pricing information for your stay</DrawerDescription>
                    </DrawerHeader>

                    <div class="overflow-y-auto px-6 pb-8">
                        <PricingBreakdownContent />
                    </div>
                </DrawerContent>
            </Drawer>

            <template v-else>
                <PricingInfo />

                <div class="mt-4 border-t pt-4">
                    <PricingBreakdownContent />
                </div>
            </template>

            <div>
                <Button size="lg" class="lg:w-full">Book Now</Button>
            </div>
        </div>
    </div> -->
</template>
