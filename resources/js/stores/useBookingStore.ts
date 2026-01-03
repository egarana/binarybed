import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

/**
 * =========================================
 * Booking Store
 * =========================================
 * 
 * Manages the booking cart/state for guest checkout flow.
 * Stores the selected resource and rate before navigating to /book.
 * 
 * Uses localStorage for persistence across page navigations.
 */

export interface BookingItem {
    resourceId: number;
    resourceType: 'units' | 'activities';
    resourceName: string;
    resourceSlug: string;
    rateId: number;
    rateName: string;
    ratePrice: number;
    currency: string;
    priceType: 'night' | 'person' | 'hourly' | 'flat';
    quantity: number;
    startDate: string | null;
    endDate: string | null;
}

export interface GuestInfo {
    name: string;
    email: string;
    phone: {
        countryCode: string;
        number: string;
    };
    notes?: string;
}

const STORAGE_KEY = 'binarybed_booking';

export const useBookingStore = defineStore('booking', () => {
    // State
    const item = ref<BookingItem | null>(null);
    const guestInfo = ref<GuestInfo | null>(null);
    const isHydrated = ref(false);

    // Getters
    const hasItem = computed(() => item.value !== null);

    const subtotal = computed(() => {
        if (!item.value) return 0;
        return item.value.ratePrice * item.value.quantity;
    });

    const nights = computed(() => {
        if (!item.value?.startDate || !item.value?.endDate) return 0;
        const start = new Date(item.value.startDate);
        const end = new Date(item.value.endDate);
        const diffTime = Math.abs(end.getTime() - start.getTime());
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    });

    const total = computed(() => {
        if (!item.value) return 0;

        // Calculate based on price type
        switch (item.value.priceType) {
            case 'night':
                return item.value.ratePrice * (nights.value || 1) * item.value.quantity;
            case 'person':
            case 'hourly':
            case 'flat':
            default:
                return item.value.ratePrice * item.value.quantity;
        }
    });

    // Actions
    function setBookingItem(newItem: BookingItem) {
        item.value = newItem;
        persistToStorage();
    }

    function setGuestInfo(info: GuestInfo) {
        guestInfo.value = info;
        persistToStorage();
    }

    function setDates(startDate: string, endDate: string) {
        if (item.value) {
            item.value.startDate = startDate;
            item.value.endDate = endDate;
            persistToStorage();
        }
    }

    function setQuantity(quantity: number) {
        if (item.value && quantity > 0) {
            item.value.quantity = quantity;
            persistToStorage();
        }
    }

    function clear() {
        item.value = null;
        guestInfo.value = null;
        removeFromStorage();
    }

    // Persistence helpers
    function persistToStorage() {
        if (typeof window !== 'undefined') {
            const data = {
                item: item.value,
                guestInfo: guestInfo.value,
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        }
    }

    function removeFromStorage() {
        if (typeof window !== 'undefined') {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    function hydrateFromStorage() {
        if (typeof window !== 'undefined' && !isHydrated.value) {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                try {
                    const data = JSON.parse(stored);
                    if (data.item) item.value = data.item;
                    if (data.guestInfo) guestInfo.value = data.guestInfo;
                } catch (e) {
                    console.warn('Failed to parse booking from storage:', e);
                    removeFromStorage();
                }
            }
            isHydrated.value = true;
        }
    }

    // Auto-hydrate on store creation (client-side only)
    if (typeof window !== 'undefined') {
        hydrateFromStorage();
    }

    return {
        // State
        item,
        guestInfo,
        isHydrated,
        // Getters
        hasItem,
        subtotal,
        nights,
        total,
        // Actions
        setBookingItem,
        setGuestInfo,
        setDates,
        setQuantity,
        clear,
        hydrateFromStorage,
    };
});
