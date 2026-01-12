<script setup lang="ts">
import { computed, ref } from 'vue';
import Layout from './Layout.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogTitle,
    DialogDescription,
    DialogHeader,
} from '@/components/ui/dialog';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetTrigger,
} from '@/components/ui/sheet';
import { 
    X, 
    ChevronLeft, 
    ChevronRight, 
    Bed, 
    Users, 
    Bath, 
    Mountain, 
    Wifi, 
    Car, 
    Coffee, 
    Utensils,
    Wind,
    Tv,
    Shirt,
    Clock,
    MapPin,
    Check,
    Ban,
    Leaf,
    Heart,
    Phone,
    MessageCircle,
    Gift,
    Sparkles,
    ChevronDown,
    BadgeCheck,
    Star,
    Award,
    Percent,
    ArrowRight,
    Zap,
    Minus,
    Plus
} from 'lucide-vue-next';

// ============================================
// ALL HARDCODED DATA FOR UI DESIGN PREVIEW
// ============================================

// Hardcoded images
const images = [
    { id: 1, url: 'https://images.unsplash.com/photo-1587061949409-02df41d5e562?w=1200', name: 'Cabin Exterior' },
    { id: 2, url: 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=800', name: 'Living Room' },
    { id: 3, url: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', name: 'Bedroom' },
    { id: 4, url: 'https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=800', name: 'Lake View' },
    { id: 5, url: 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=800', name: 'Bathroom' },
    { id: 6, url: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800', name: 'Mountain View' },
];

// Hardcoded property info
const property = {
    name: 'Lakefront Cabin with Volcano View',
    type: 'Entire cabin',
    location: 'Songan A, Kintamani, Bangli, Bali',
    description: `Escape to this stunning lakefront cabin with breathtaking views of Mount Batur. Wake up to misty mornings and spectacular sunrises over the volcanic crater lake.

This cozy cabin offers the perfect blend of rustic charm and modern comfort. The open-plan living area features floor-to-ceiling windows that frame the incredible panorama, while the fully-equipped kitchen lets you prepare meals with locally-sourced ingredients.

Step outside onto your private balcony and immerse yourself in the tranquil sounds of nature. Whether you're seeking adventure or relaxation, this is your perfect retreat.`,
};

// Hardcoded highlights
const highlights = [
    { icon: Bed, label: '2 Bedrooms' },
    { icon: Users, label: '4 Guests' },
    { icon: Bath, label: '1 Bathroom' },
    { icon: Mountain, label: 'Lake View' },
];

// Hardcoded amenities (for preview, show first 6)
const amenities = [
    { icon: Wifi, name: 'Free WiFi', description: 'Fiber optic high-speed' },
    { icon: Car, name: 'Free Parking', description: 'Private parking on-site' },
    { icon: Coffee, name: 'Coffee Maker', description: 'Nespresso machine' },
    { icon: Utensils, name: 'Full Kitchen', description: 'Stove, oven, fridge' },
    { icon: Wind, name: 'Air Conditioning', description: 'All rooms' },
    { icon: Tv, name: 'Smart TV', description: 'Netflix included' },
    { icon: Shirt, name: 'Washer & Dryer', description: 'Free laundry' },
    { icon: Mountain, name: 'Balcony', description: 'Lake view' },
];

// Hardcoded features by category (simulating the attach features system)
const featuresByCategory = {
    inclusion: [
        { name: 'Welcome drink', description: 'Traditional Balinese coffee' },
        { name: 'Daily breakfast', description: 'Local and Western options' },
        { name: 'Airport transfer', description: 'One-way pickup included' },
        { name: 'Kayak & SUP', description: 'Free equipment rental' },
    ],
    exclusion: [
        { name: 'Lunch and dinner', description: 'Can be arranged on request' },
        { name: 'Spa treatments', description: 'Available for additional fee' },
        { name: 'Trip to Mount Batur', description: 'Guide available for hire' },
    ],
    suggestion: [
        { name: 'Warm clothing', description: 'Cool mountain temperatures' },
        { name: 'Sunscreen', description: 'Strong UV at altitude' },
        { name: 'Camera', description: 'Stunning photo opportunities' },
        { name: 'Hiking shoes', description: 'For volcano trekking' },
    ],
};

// Hardcoded unique selling points (differentiator)
const uniqueFeatures = [
    { icon: Leaf, title: 'Eco-Friendly', description: 'Solar powered with rainwater harvesting' },
    { icon: Heart, title: 'Family Owned', description: 'Authentic local hospitality since 2019' },
    { icon: Phone, title: 'Direct Contact', description: 'Chat with host anytime, no middleman' },
    { icon: Gift, title: 'Welcome Package', description: 'Local snacks & handmade souvenirs' },
];

// Hardcoded house rules
const houseRules = [
    { icon: Clock, text: 'Check-in: 2:00 PM - 8:00 PM' },
    { icon: Clock, text: 'Checkout: 11:00 AM' },
    { icon: Users, text: 'Maximum 4 guests' },
    { icon: Ban, text: 'No smoking' },
    { icon: Ban, text: 'No pets allowed' },
    { icon: Ban, text: 'No parties or events' },
];

// Hardcoded rates
const rates = [
    { id: 1, name: 'Standard Rate', price: 1500000, originalPrice: 1800000, currency: 'IDR', priceType: 'night', description: 'Best for short stays', isDefault: true },
    { id: 2, name: 'Weekly Rate', price: 1200000, originalPrice: 1500000, currency: 'IDR', priceType: 'night', description: 'Save 20% on 7+ nights', isDefault: false },
    { id: 3, name: 'Monthly Rate', price: 900000, originalPrice: 1500000, currency: 'IDR', priceType: 'night', description: 'Best value for long stays', isDefault: false },
];

// Hardcoded booking state (for demo)
const nights = ref(2);
const checkInDate = ref('Jan 15');
const checkOutDate = ref('Jan 17');

// Guest selection
const adults = ref(2);
const children = ref(0);
const infants = ref(0);
const totalGuests = computed(() => adults.value + children.value);
const isGuestSelectorOpen = ref(false);

function adjustGuests(type: 'adults' | 'children' | 'infants', delta: number) {
    if (type === 'adults') {
        adults.value = Math.max(1, Math.min(4, adults.value + delta));
    } else if (type === 'children') {
        children.value = Math.max(0, Math.min(4 - adults.value, children.value + delta));
    } else {
        infants.value = Math.max(0, Math.min(2, infants.value + delta));
    }
}

// Hardcoded location
const locationInfo = {
    address: 'Songan A, Kintamani, Bangli',
    distance: '2 hours from Ngurah Rai Airport',
    highlights: ['5 min walk to Lake Batur', 'Near traditional village', 'Quiet neighborhood'],
};

// Host information
const hostInfo = {
    name: 'Made Wirawan',
    photo: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face',
    since: '2019',
    responseTime: '< 1 hour',
    languages: ['English', 'Indonesian', 'Japanese'],
    story: 'Born and raised in Bali, I built this cabin to share the magic of Lake Batur with travelers. Every morning, I wake up to the volcano sunrise and I wanted others to experience this too.',
    whatsapp: '+6281234567890',
};

// Guest testimonials (simplified - no external photos)
const testimonials = [
    {
        name: 'Sarah M.',
        country: 'Australia',
        date: 'Dec 2025',
        rating: 5,
        text: 'Absolutely magical! The sunrise view was incredible and Made was so helpful.',
        initials: 'SM',
    },
    {
        name: 'Thomas K.',
        country: 'Germany', 
        date: 'Nov 2025',
        rating: 5,
        text: 'Best decision to book direct. We got early check-in and the welcome package was lovely!',
        initials: 'TK',
    },
    {
        name: 'Yuki T.',
        country: 'Japan',
        date: 'Oct 2025', 
        rating: 5,
        text: 'Made-san was so kind. The cabin is even more beautiful than photos!',
        initials: 'YT',
    },
];

// Exclusive perks for direct booking
const exclusivePerks = [
    { icon: Clock, title: 'Flexible Check-in', description: 'Early check-in from 11 AM (subject to availability)' },
    { icon: Gift, title: 'Welcome Package', description: 'Local snacks, Bali coffee & handmade souvenirs' },
    { icon: Zap, title: 'Room Upgrade', description: 'Complimentary upgrade when available' },
    { icon: BadgeCheck, title: 'Free Cancellation', description: 'Cancel up to 48 hours before for full refund' },
];

// Packing tips (simple, easy to maintain)
const packingTips = [
    'Warm jacket (cool mountain nights)',
    'Hiking shoes',
    'Sunscreen',
    'Camera',
];

// Loyalty program
const loyaltyProgram = {
    discount: '10%',
    description: 'Book again within 12 months',
};

// OTA comparison (simplified - just percentage)
const otaSavings = 15; // percent

// ============================================
// COMPONENT STATE
// ============================================

// Modal states
const isGalleryOpen = ref(false);
const currentImageIndex = ref(0);
const isAmenitiesOpen = ref(false);
const isFeaturesOpen = ref(false);

// Description expansion
const isDescriptionExpanded = ref(false);

// Split description into paragraphs
const paragraphs = computed(() => property.description.split(/\n\n+/).filter(p => p.trim()));
const hasMultipleParagraphs = computed(() => paragraphs.value.length > 1);

const truncatedDescription = computed(() => {
    if (isDescriptionExpanded.value || !hasMultipleParagraphs.value) {
        return property.description;
    }
    return paragraphs.value[0];
});

// Rate selection
const selectedRateId = ref(1);
const selectedRate = computed(() => rates.find(r => r.id === selectedRateId.value) || rates[0]);

// Gallery functions
const openGallery = (index: number = 0) => {
    currentImageIndex.value = index;
    isGalleryOpen.value = true;
};

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % images.length;
};

const prevImage = () => {
    currentImageIndex.value = currentImageIndex.value === 0 ? images.length - 1 : currentImageIndex.value - 1;
};

// Format currency (tanpa desimal untuk tampilan yang lebih rapi)
const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0 
    }).format(amount);
};
</script>

<template>
    <Layout title="Lakefront Cabin">

        <!-- Content Area -->
        <section class="py-8 md:py-12">
            <div class="mx-auto max-w-6xl px-6">
                <div class="lg:grid lg:grid-cols-5 lg:gap-0">
                    <!-- Left Column: Content -->
                    <div class="lg:col-span-3 space-y-8">
                        <!-- Mobile: Single image -->
                        <div class="md:hidden relative aspect-[4/3]">
                            <img 
                                :src="images[0].url" 
                                :alt="images[0].name"
                                class="w-full h-full object-cover cursor-pointer"
                                @click="openGallery(0)"
                            />
                            <button 
                                @click="openGallery(0)"
                                class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-white transition-colors"
                            >
                                Show all {{ images.length }} photos
                            </button>
                        </div>
                        
                        <!-- Desktop: Grid gallery -->
                        <div class="hidden md:grid md:grid-cols-4 md:grid-rows-2 gap-2 h-[480px] p-0 relative">
                            <!-- Main large image -->
                            <div class="col-span-2 row-span-2 relative rounded-l-xl overflow-hidden">
                                <img 
                                    :src="images[0].url" 
                                    :alt="images[0].name"
                                    class="w-full h-full object-cover hover:brightness-95 transition-all cursor-pointer"
                                    @click="openGallery(0)"
                                />
                            </div>
                            <!-- Smaller images -->
                            <template v-for="(image, index) in images.slice(1, 5)" :key="image.id">
                                <div 
                                    class="relative overflow-hidden cursor-pointer"
                                    :class="{
                                        'rounded-tr-xl': index === 1,
                                        'rounded-br-xl': index === 3
                                    }"
                                    @click="openGallery(index + 1)"
                                >
                                    <img 
                                        :src="image.url" 
                                        :alt="image.name"
                                        class="w-full h-full object-cover hover:brightness-95 transition-all"
                                    />
                                </div>
                            </template>
                            <!-- Show all photos button -->
                            <button 
                                @click="openGallery(0)"
                                class="absolute bottom-10 right-10 bg-white px-4 py-2 rounded-lg text-sm font-medium shadow-md hover:shadow-lg transition-shadow"
                            >
                                Show all {{ images.length }} photos
                            </button>
                        </div>
                        <!-- Header -->
                        <header class="border-b border-stone-200 pb-8">
                            <p class="text-sm uppercase tracking-wider text-stone-500 mb-2">
                                Entire cabin
                            </p>
                            <h1 class="text-3xl md:text-4xl font-light text-stone-900 mb-4">
                                Lakefront Cabin with Volcano View
                            </h1>
                            
                            <!-- Highlights -->
                            <div class="flex flex-wrap gap-4 text-stone-600">
                                <div class="flex items-center gap-2">
                                    <Bed class="w-5 h-5" />
                                    <span>2 Bedrooms</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Users class="w-5 h-5" />
                                    <span>4 Guests</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Bath class="w-5 h-5" />
                                    <span>1 Bathroom</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Mountain class="w-5 h-5" />
                                    <span>Lake View</span>
                                </div>
                            </div>
                        </header>

                        <!-- Unique Selling Points (Differentiator) -->
                        <div class="grid grid-cols-2 gap-4 p-6 bg-gradient-to-br from-stone-50 to-amber-50/30 rounded-2xl border border-stone-100">
                            <div v-for="feature in uniqueFeatures" :key="feature.title" class="flex items-start gap-3">
                                <div class="p-2 bg-white rounded-lg shadow-sm">
                                    <component :is="feature.icon" class="w-5 h-5 text-amber-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-stone-900 text-sm">{{ feature.title }}</p>
                                    <p class="text-xs text-stone-500">{{ feature.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Description with Read More -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">About this place</h2>
                            <p class="text-stone-600 font-light leading-relaxed whitespace-pre-line">
                                {{ truncatedDescription }}
                            </p>
                            <button 
                                v-if="hasMultipleParagraphs"
                                @click="isDescriptionExpanded = !isDescriptionExpanded"
                                class="flex items-center gap-1 text-stone-900 font-medium underline hover:no-underline"
                            >
                                {{ isDescriptionExpanded ? 'Show less' : 'Read more' }}
                                <ChevronDown 
                                    class="w-4 h-4 transition-transform" 
                                    :class="{ 'rotate-180': isDescriptionExpanded }"
                                />
                            </button>
                        </div>

                        <!-- Amenities Preview + Dialog -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">What this place offers</h2>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <li 
                                    v-for="amenity in amenities.slice(0, 6)" 
                                    :key="amenity.name"
                                    class="flex items-center gap-4 text-stone-700"
                                >
                                    <component :is="amenity.icon" class="w-6 h-6 text-stone-500" />
                                    <span>{{ amenity.name }}</span>
                                </li>
                            </ul>
                            <Button variant="outline" @click="isAmenitiesOpen = true" class="mt-4">
                                Show all {{ amenities.length }} amenities
                            </Button>
                        </div>

                        <!-- Features Preview + Dialog (Inclusions, Exclusions, Suggestions) -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-medium text-stone-900">What's included & more</h2>
                                <Button variant="ghost" size="sm" @click="isFeaturesOpen = true" class="text-stone-600">
                                    View all
                                </Button>
                            </div>
                            <!-- Quick preview -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 bg-green-50 rounded-xl">
                                    <p class="text-sm font-medium text-green-800 mb-2 flex items-center gap-2">
                                        <Check class="w-4 h-4" /> Included
                                    </p>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li v-for="item in featuresByCategory.inclusion.slice(0, 2)" :key="item.name">
                                            {{ item.name }}
                                        </li>
                                        <li v-if="featuresByCategory.inclusion.length > 2" class="text-green-600">
                                            +{{ featuresByCategory.inclusion.length - 2 }} more
                                        </li>
                                    </ul>
                                </div>
                                <div class="p-4 bg-red-50 rounded-xl">
                                    <p class="text-sm font-medium text-red-800 mb-2 flex items-center gap-2">
                                        <Ban class="w-4 h-4" /> Not included
                                    </p>
                                    <ul class="text-sm text-red-700 space-y-1">
                                        <li v-for="item in featuresByCategory.exclusion.slice(0, 2)" :key="item.name">
                                            {{ item.name }}
                                        </li>
                                        <li v-if="featuresByCategory.exclusion.length > 2" class="text-red-600">
                                            +{{ featuresByCategory.exclusion.length - 2 }} more
                                        </li>
                                    </ul>
                                </div>
                                <div class="p-4 bg-amber-50 rounded-xl">
                                    <p class="text-sm font-medium text-amber-800 mb-2 flex items-center gap-2">
                                        <Sparkles class="w-4 h-4" /> Suggestions
                                    </p>
                                    <ul class="text-sm text-amber-700 space-y-1">
                                        <li v-for="item in featuresByCategory.suggestion.slice(0, 2)" :key="item.name">
                                            {{ item.name }}
                                        </li>
                                        <li v-if="featuresByCategory.suggestion.length > 2" class="text-amber-600">
                                            +{{ featuresByCategory.suggestion.length - 2 }} more
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Location (without map) -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">Location</h2>
                            <div class="flex items-start gap-3 text-stone-600">
                                <MapPin class="w-5 h-5 mt-0.5" />
                                <div>
                                    <p class="font-medium text-stone-900">{{ locationInfo.address }}</p>
                                    <p class="text-sm text-stone-500">{{ locationInfo.distance }}</p>
                                </div>
                            </div>
                            <ul class="flex flex-wrap gap-2">
                                <li 
                                    v-for="item in locationInfo.highlights" 
                                    :key="item"
                                    class="px-3 py-1.5 bg-stone-100 rounded-full text-sm text-stone-600"
                                >
                                    {{ item }}
                                </li>
                            </ul>
                        </div>

                        <!-- House Rules -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">House rules</h2>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <li 
                                    v-for="rule in houseRules" 
                                    :key="rule.text"
                                    class="flex items-center gap-3 text-stone-600"
                                >
                                    <component :is="rule.icon" class="w-5 h-5 text-stone-500" />
                                    <span>{{ rule.text }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- ============================================ -->
                        <!-- DIFFERENTIATOR SECTIONS -->
                        <!-- ============================================ -->

                        <!-- Exclusive Perks (Book Direct Benefits) -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-medium text-stone-900">Book direct & get more</h2>
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Exclusive</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div 
                                    v-for="perk in exclusivePerks" 
                                    :key="perk.title"
                                    class="flex items-start gap-3 p-4 bg-green-50 rounded-xl border border-green-100"
                                >
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <component :is="perk.icon" class="w-5 h-5 text-green-600" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ perk.title }}</p>
                                        <p class="text-sm text-stone-500">{{ perk.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OTA Price Comparison -->
                        <div class="p-6 bg-gradient-to-r from-stone-900 to-stone-800 rounded-2xl text-white border-b border-stone-200 mb-8">
                            <div class="flex items-center gap-2 mb-4">
                                <Percent class="w-5 h-5" />
                                <span class="font-medium">Save vs OTA</span>
                            </div>
                            <div class="text-center">
                                <p class="text-4xl font-bold text-green-400 mb-2">{{ otaSavings }}%</p>
                                <p class="text-stone-300">Lower than Booking.com & Airbnb</p>
                            </div>
                        </div>

                        <!-- Host Story -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">Meet your host</h2>
                            <div class="flex items-start gap-4">
                                <img 
                                    :src="hostInfo.photo" 
                                    :alt="hostInfo.name"
                                    class="w-16 h-16 rounded-full object-cover"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-semibold text-stone-900">{{ hostInfo.name }}</p>
                                        <span class="px-2 py-0.5 bg-stone-100 text-stone-600 text-xs rounded-full">
                                            Host since {{ hostInfo.since }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-stone-500 mb-3">
                                        <span class="flex items-center gap-1">
                                            <Clock class="w-4 h-4" />
                                            Responds {{ hostInfo.responseTime }}
                                        </span>
                                        <span>{{ hostInfo.languages.join(', ') }}</span>
                                    </div>
                                    <p class="text-stone-600 text-sm leading-relaxed">
                                        "{{ hostInfo.story }}"
                                    </p>
                                </div>
                            </div>
                            <a 
                                :href="`https://wa.me/${hostInfo.whatsapp.replace(/\+/g, '')}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                            >
                                <MessageCircle class="w-4 h-4" />
                                Chat on WhatsApp
                            </a>
                        </div>

                        <!-- Guest Testimonials -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-medium text-stone-900">What guests say</h2>
                                <div class="flex items-center gap-1">
                                    <Star class="w-5 h-5 text-yellow-500 fill-yellow-500" />
                                    <span class="font-semibold">5.0</span>
                                    <span class="text-stone-500 text-sm">({{ testimonials.length }} reviews)</span>
                                </div>
                            </div>
                            <div class="grid gap-4">
                                <div 
                                    v-for="review in testimonials" 
                                    :key="review.name"
                                    class="p-4 bg-stone-50 rounded-xl"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-full bg-stone-200 flex items-center justify-center text-stone-600 font-semibold text-sm">
                                            {{ review.initials }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <div>
                                                    <p class="font-medium text-stone-900">{{ review.name }}</p>
                                                    <p class="text-xs text-stone-500">{{ review.country }} · {{ review.date }}</p>
                                                </div>
                                                <div class="flex items-center gap-0.5">
                                                    <Star v-for="n in review.rating" :key="n" class="w-4 h-4 text-yellow-500 fill-yellow-500" />
                                                </div>
                                            </div>
                                            <p class="text-sm text-stone-600">"{{ review.text }}"</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Local Events & Weather -->
                        <!-- Packing Tips -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">Don't forget to pack</h2>
                            <ul class="grid grid-cols-2 gap-2">
                                <li 
                                    v-for="tip in packingTips" 
                                    :key="tip"
                                    class="flex items-center gap-2 text-sm text-stone-600"
                                >
                                    <Check class="w-4 h-4 text-green-500" />
                                    {{ tip }}
                                </li>
                            </ul>
                        </div>

                        <!-- Loyalty Program -->
                        <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-amber-100 rounded-full">
                                    <Award class="w-6 h-6 text-amber-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-stone-900">Return Guest Reward</p>
                                    <p class="text-sm text-stone-600">
                                        {{ loyaltyProgram.description }} and get <span class="font-bold text-amber-600">{{ loyaltyProgram.discount }} off</span> your next stay
                                    </p>
                                </div>
                                <ArrowRight class="w-5 h-5 text-stone-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Pricing Card -->
                    <div class="hidden lg:block lg:col-span-2 lg:ps-16">
                        <div class="sticky top-8 bg-white border border-stone-200 rounded-xl p-6 shadow-lg">
                            <!-- Price display -->
                            <div class="mb-4">
                                <p class="flex items-baseline gap-2">
                                    <span class="text-stone-400 line-through text-sm">
                                        {{ formatCurrency(selectedRate.originalPrice * nights, selectedRate.currency) }}
                                    </span>
                                    <span class="text-2xl font-semibold text-stone-900">
                                        {{ formatCurrency(selectedRate.price * nights + 350000, selectedRate.currency) }}
                                    </span>
                                </p>
                                <p class="text-sm text-stone-500 mt-1">
                                    {{ checkInDate }} - {{ checkOutDate }} · {{ nights }} nights · <span class="text-green-600 font-medium">Includes taxes & fees</span>
                                </p>
                            </div>

                            <!-- Direct Booking Benefits Badge -->
                            <div class="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-lg mb-4">
                                <BadgeCheck class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                                <div>
                                    <p class="text-sm font-medium text-emerald-800">Direct Booking Benefits</p>
                                    <p class="text-xs text-emerald-600">Best rate + no middleman fees</p>
                                </div>
                            </div>

                            <!-- Date & Guest picker -->
                            <div class="border border-stone-300 rounded-xl overflow-hidden mb-4">
                                <div class="grid grid-cols-2 divide-x divide-stone-300">
                                    <div class="p-3 cursor-pointer hover:bg-stone-50">
                                        <p class="text-xs font-medium uppercase text-stone-700">Check-in</p>
                                        <p class="text-sm font-medium">{{ checkInDate }}</p>
                                    </div>
                                    <div class="p-3 cursor-pointer hover:bg-stone-50">
                                        <p class="text-xs font-medium uppercase text-stone-700">Checkout</p>
                                        <p class="text-sm font-medium">{{ checkOutDate }}</p>
                                    </div>
                                </div>
                                <div class="border-t border-stone-300 relative">
                                    <button 
                                        @click="isGuestSelectorOpen = !isGuestSelectorOpen"
                                        class="w-full p-3 text-left hover:bg-stone-50 flex items-center justify-between"
                                    >
                                        <div>
                                            <p class="text-xs font-medium uppercase text-stone-700">Guests</p>
                                            <p class="text-sm font-medium">
                                                {{ totalGuests }} guest{{ totalGuests > 1 ? 's' : '' }}
                                                <span v-if="infants > 0" class="text-stone-500">, {{ infants }} infant{{ infants > 1 ? 's' : '' }}</span>
                                            </p>
                                        </div>
                                        <ChevronDown class="w-4 h-4 text-stone-400 transition-transform" :class="{ 'rotate-180': isGuestSelectorOpen }" />
                                    </button>
                                    
                                    <!-- Guest selector dropdown -->
                                    <div 
                                        v-if="isGuestSelectorOpen"
                                        class="absolute left-0 right-0 top-full z-20 bg-white border border-stone-200 rounded-xl shadow-lg p-4 mt-2"
                                    >
                                        <!-- Adults -->
                                        <div class="flex items-center justify-between py-3 border-b border-stone-100">
                                            <div>
                                                <p class="font-medium text-stone-900">Adults</p>
                                                <p class="text-xs text-stone-500">Age 13+</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button 
                                                    @click="adjustGuests('adults', -1)"
                                                    :disabled="adults <= 1"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Minus class="w-3 h-3" />
                                                </button>
                                                <span class="w-6 text-center font-semibold">{{ adults }}</span>
                                                <button 
                                                    @click="adjustGuests('adults', 1)"
                                                    :disabled="adults >= 4"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Plus class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Children -->
                                        <div class="flex items-center justify-between py-3 border-b border-stone-100">
                                            <div>
                                                <p class="font-medium text-stone-900">Children</p>
                                                <p class="text-xs text-stone-500">Age 2-12</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button 
                                                    @click="adjustGuests('children', -1)"
                                                    :disabled="children <= 0"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Minus class="w-3 h-3" />
                                                </button>
                                                <span class="w-6 text-center font-semibold">{{ children }}</span>
                                                <button 
                                                    @click="adjustGuests('children', 1)"
                                                    :disabled="totalGuests >= 4"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Plus class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Infants -->
                                        <div class="flex items-center justify-between py-3">
                                            <div>
                                                <p class="font-medium text-stone-900">Infants</p>
                                                <p class="text-xs text-stone-500">Under 2</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button 
                                                    @click="adjustGuests('infants', -1)"
                                                    :disabled="infants <= 0"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Minus class="w-3 h-3" />
                                                </button>
                                                <span class="w-6 text-center font-semibold">{{ infants }}</span>
                                                <button 
                                                    @click="adjustGuests('infants', 1)"
                                                    :disabled="infants >= 2"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Plus class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <p class="text-xs text-stone-500 mt-3">This cabin allows up to 4 guests, plus 2 infants.</p>
                                        
                                        <Button 
                                            @click="isGuestSelectorOpen = false"
                                            size="sm"
                                            class="w-full mt-3 bg-stone-900 hover:bg-stone-800"
                                        >
                                            Done
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Rate options -->
                            <div class="space-y-2 mb-4">
                                <p class="text-sm font-medium text-stone-700 mb-3">Select rate</p>
                                <button 
                                    v-for="rate in rates" 
                                    :key="rate.id"
                                    @click="selectedRateId = rate.id"
                                    class="w-full flex justify-between items-center p-3 rounded-lg border-2 transition-all text-left"
                                    :class="selectedRateId === rate.id 
                                        ? 'border-stone-900 bg-stone-50' 
                                        : 'border-stone-200 hover:border-stone-300'"
                                >
                                    <div class="flex items-center gap-3">
                                        <div 
                                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                            :class="selectedRateId === rate.id ? 'border-stone-900 bg-stone-900' : 'border-stone-300'"
                                        >
                                            <Check v-if="selectedRateId === rate.id" class="w-3 h-3 text-white" />
                                        </div>
                                        <div>
                                            <span class="font-medium text-stone-900">{{ rate.name }}</span>
                                            <p class="text-xs text-stone-500">{{ rate.description }}</p>
                                        </div>
                                    </div>
                                    <span class="font-semibold text-stone-900 text-right">
                                        {{ formatCurrency(rate.price, rate.currency) }}
                                        <span class="text-xs font-normal text-stone-500 block">/{{ rate.priceType }}</span>
                                    </span>
                                </button>
                            </div>

                            <!-- CTA -->
                            <Button 
                                size="lg" 
                                class="w-full bg-stone-900 hover:bg-stone-800 text-white"
                            >
                                Reserve
                            </Button>

                            <p class="text-xs text-center text-stone-500 mt-4">
                                You won't be charged yet
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mobile: Fixed bottom pricing bar -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-50">
            <!-- Direct Booking Banner -->
            <div class="bg-emerald-600 text-white text-center py-1.5 text-xs font-medium">
                <BadgeCheck class="w-3.5 h-3.5 inline mr-1" />
                Direct Booking Benefits — Best Rate, No Hidden Fees
            </div>
            
            <!-- Pricing Bar -->
            <div class="bg-white border-t border-stone-100 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
                <Sheet>
                    <SheetTrigger as-child>
                        <button class="w-full px-4 py-3 flex items-center justify-between">
                            <div class="text-left">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-stone-900">
                                        {{ formatCurrency(selectedRate.price * nights + 350000, selectedRate.currency) }}
                                    </span>
                                    <span class="text-xs text-stone-400 line-through">
                                        {{ formatCurrency(selectedRate.originalPrice * nights, selectedRate.currency) }}
                                    </span>
                                </div>
                                <p class="text-xs text-stone-500 mt-0.5">
                                    {{ checkInDate }} – {{ checkOutDate }} · <span class="text-green-600">Incl. taxes</span>
                                </p>
                            </div>
                            <Button size="lg" class="bg-stone-900 hover:bg-stone-800 text-white shadow-lg flex-shrink-0">
                                Reserve
                            </Button>
                        </button>
                    </SheetTrigger>
                    <SheetContent side="bottom" class="h-[85vh] rounded-t-3xl">
                        <SheetHeader class="pb-4 border-b border-stone-200">
                            <SheetTitle class="text-xl">Pricing Details</SheetTitle>
                            <SheetDescription class="sr-only">View rate options and price breakdown</SheetDescription>
                        </SheetHeader>
                        
                        <div class="py-6 space-y-6 overflow-y-auto">
                            <!-- Rate Selection -->
                            <div class="space-y-3">
                                <p class="font-medium text-stone-900">Select rate</p>
                                <div class="space-y-2">
                                    <button 
                                        v-for="rate in rates" 
                                        :key="rate.id"
                                        @click="selectedRateId = rate.id"
                                        class="w-full flex justify-between items-center p-4 rounded-xl border-2 transition-all text-left"
                                        :class="selectedRateId === rate.id 
                                            ? 'border-stone-900 bg-stone-50' 
                                            : 'border-stone-200'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div 
                                                class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                                :class="selectedRateId === rate.id ? 'border-stone-900 bg-stone-900' : 'border-stone-300'"
                                            >
                                                <Check v-if="selectedRateId === rate.id" class="w-3 h-3 text-white" />
                                            </div>
                                            <div>
                                                <span class="font-medium text-stone-900">{{ rate.name }}</span>
                                                <p class="text-xs text-stone-500">{{ rate.description }}</p>
                                            </div>
                                        </div>
                                        <span class="font-semibold text-stone-900 text-right">
                                            {{ formatCurrency(rate.price, rate.currency) }}
                                            <span class="text-xs font-normal text-stone-500 block">/{{ rate.priceType }}</span>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Date & Guests -->
                            <div class="space-y-3">
                                <p class="font-medium text-stone-900">Stay details</p>
                                <div class="border border-stone-200 rounded-xl overflow-hidden">
                                    <div class="grid grid-cols-2 divide-x divide-stone-200">
                                        <div class="p-4">
                                            <p class="text-xs font-medium uppercase text-stone-500">Check-in</p>
                                            <p class="font-medium">{{ checkInDate }}</p>
                                        </div>
                                        <div class="p-4">
                                            <p class="text-xs font-medium uppercase text-stone-500">Checkout</p>
                                            <p class="font-medium">{{ checkOutDate }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Guest selector inline -->
                                <div class="border border-stone-200 rounded-xl p-4 space-y-4">
                                    <p class="font-medium text-stone-900">Guests</p>
                                    
                                    <!-- Adults -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-stone-900">Adults</p>
                                            <p class="text-xs text-stone-500">Age 13+</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button 
                                                @click="adjustGuests('adults', -1)"
                                                :disabled="adults <= 1"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </button>
                                            <span class="w-6 text-center font-semibold">{{ adults }}</span>
                                            <button 
                                                @click="adjustGuests('adults', 1)"
                                                :disabled="adults >= 4"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Children -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-stone-900">Children</p>
                                            <p class="text-xs text-stone-500">Age 2-12</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button 
                                                @click="adjustGuests('children', -1)"
                                                :disabled="children <= 0"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </button>
                                            <span class="w-6 text-center font-semibold">{{ children }}</span>
                                            <button 
                                                @click="adjustGuests('children', 1)"
                                                :disabled="totalGuests >= 4"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Infants -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-stone-900">Infants</p>
                                            <p class="text-xs text-stone-500">Under 2</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button 
                                                @click="adjustGuests('infants', -1)"
                                                :disabled="infants <= 0"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </button>
                                            <span class="w-6 text-center font-semibold">{{ infants }}</span>
                                            <button 
                                                @click="adjustGuests('infants', 1)"
                                                :disabled="infants >= 2"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-stone-500">Max 4 guests, plus 2 infants.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom CTA -->
                        <div class="pt-4 border-t border-stone-200">
                            <Button size="lg" class="w-full bg-stone-900 hover:bg-stone-800 text-white">
                                Reserve
                            </Button>
                            <p class="text-xs text-center text-stone-500 mt-3">You won't be charged yet</p>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </div>

        <!-- Spacer for mobile fixed bar (includes banner height) -->
        <div class="h-28 lg:hidden"></div>

        <!-- ============================================ -->
        <!-- DIALOGS / MODALS -->
        <!-- ============================================ -->

        <!-- Photo Gallery Modal -->
        <Dialog v-model:open="isGalleryOpen">
            <DialogContent class="max-w-[100vw] h-[100vh] p-0 bg-black border-0 rounded-none">
                <DialogTitle class="sr-only">Photo Gallery</DialogTitle>
                <DialogDescription class="sr-only">Browse all photos</DialogDescription>
                
                <!-- Close button -->
                <button 
                    @click="isGalleryOpen = false"
                    class="absolute top-4 left-4 z-50 p-2 bg-white/10 hover:bg-white/20 rounded-full transition-colors"
                >
                    <X class="w-6 h-6 text-white" />
                </button>

                <!-- Image counter -->
                <div class="absolute top-4 left-1/2 -translate-x-1/2 z-50 text-white text-sm">
                    {{ currentImageIndex + 1 }} / {{ images.length }}
                </div>

                <!-- Navigation buttons -->
                <button 
                    @click="prevImage"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-50 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-colors"
                >
                    <ChevronLeft class="w-8 h-8 text-white" />
                </button>
                <button 
                    @click="nextImage"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-50 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-colors"
                >
                    <ChevronRight class="w-8 h-8 text-white" />
                </button>

                <!-- Current image -->
                <div class="h-full w-full flex items-center justify-center p-8">
                    <img 
                        :src="images[currentImageIndex].url"
                        :alt="images[currentImageIndex].name"
                        class="max-h-full max-w-full object-contain"
                    />
                </div>

                <!-- Thumbnail strip -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 p-2 bg-black/50 rounded-lg">
                    <button
                        v-for="(image, index) in images"
                        :key="image.id"
                        @click="currentImageIndex = index"
                        class="w-16 h-12 rounded overflow-hidden transition-all"
                        :class="currentImageIndex === index ? 'ring-2 ring-white' : 'opacity-60 hover:opacity-100'"
                    >
                        <img :src="image.url" :alt="image.name" class="w-full h-full object-cover" />
                    </button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Amenities Dialog -->
        <Dialog v-model:open="isAmenitiesOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>What this place offers</DialogTitle>
                    <DialogDescription>All amenities available at this property</DialogDescription>
                </DialogHeader>
                <div class="py-4 max-h-[60vh] overflow-y-auto">
                    <ul class="space-y-4">
                        <li 
                            v-for="amenity in amenities" 
                            :key="amenity.name"
                            class="flex items-start gap-4"
                        >
                            <component :is="amenity.icon" class="w-6 h-6 text-stone-500 mt-0.5" />
                            <div>
                                <p class="font-medium text-stone-900">{{ amenity.name }}</p>
                                <p class="text-sm text-stone-500">{{ amenity.description }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Features Dialog (Inclusions, Exclusions, Suggestions) -->
        <Dialog v-model:open="isFeaturesOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>What's included & more</DialogTitle>
                    <DialogDescription>Everything you need to know before booking</DialogDescription>
                </DialogHeader>
                <div class="py-4 max-h-[60vh] overflow-y-auto space-y-6">
                    <!-- Inclusions -->
                    <div>
                        <h3 class="font-medium text-stone-900 mb-3 flex items-center gap-2">
                            <Check class="w-5 h-5 text-green-600" />
                            What's Included
                        </h3>
                        <ul class="space-y-3 pl-7">
                            <li v-for="item in featuresByCategory.inclusion" :key="item.name" class="text-stone-600">
                                <span class="font-medium text-stone-900">{{ item.name }}</span>
                                <span v-if="item.description" class="text-sm text-stone-500 block">{{ item.description }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Exclusions -->
                    <div>
                        <h3 class="font-medium text-stone-900 mb-3 flex items-center gap-2">
                            <Ban class="w-5 h-5 text-red-600" />
                            Not Included
                        </h3>
                        <ul class="space-y-3 pl-7">
                            <li v-for="item in featuresByCategory.exclusion" :key="item.name" class="text-stone-600">
                                <span class="font-medium text-stone-900">{{ item.name }}</span>
                                <span v-if="item.description" class="text-sm text-stone-500 block">{{ item.description }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Suggestions -->
                    <div>
                        <h3 class="font-medium text-stone-900 mb-3 flex items-center gap-2">
                            <Sparkles class="w-5 h-5 text-amber-600" />
                            What to Bring
                        </h3>
                        <ul class="space-y-3 pl-7">
                            <li v-for="item in featuresByCategory.suggestion" :key="item.name" class="text-stone-600">
                                <span class="font-medium text-stone-900">{{ item.name }}</span>
                                <span v-if="item.description" class="text-sm text-stone-500 block">{{ item.description }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </Layout>
</template>
