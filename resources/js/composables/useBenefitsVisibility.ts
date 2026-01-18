import { ref } from 'vue';

/**
 * Shared state for booking direct benefits visibility
 * Used to sync visibility state between ProductPricingCard and ProductDetail
 */
const isBenefitsHidden = ref(false);

export function useBenefitsVisibility() {
    return {
        isBenefitsHidden
    };
}
