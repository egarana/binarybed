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
    Clock, 
    Users, 
    Mountain, 
    Sunrise,
    Camera,
    Footprints,
    Globe,
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
    Plus,
    Calendar,
    Shield,
    Utensils,
    Droplets,
    ThermometerSun
} from 'lucide-vue-next';

// ============================================
// ALL HARDCODED DATA FOR ACTIVITY UI PREVIEW
// ============================================

// Activity images
const images = [
    { id: 1, url: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200', name: 'Sunrise at Mount Batur' },
    { id: 2, url: 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800', name: 'Trekking Path' },
    { id: 3, url: 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=800', name: 'Group Hiking' },
    { id: 4, url: 'https://images.unsplash.com/photo-1469521669194-babb45599def?w=800', name: 'Crater View' },
    { id: 5, url: 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800', name: 'Mountain Summit' },
    { id: 6, url: 'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800', name: 'Volcanic Landscape' },
];

// Activity info
const activity = {
    name: 'Mount Batur Sunrise Trek',
    type: 'Guided Adventure',
    location: 'Mount Batur, Kintamani, Bali',
    description: `Experience the magical sunrise from the summit of Mount Batur, Bali's most iconic active volcano. This unforgettable trek begins in the early hours of the morning, hiking under the stars as you make your way to the 1,717-meter peak.

As the first rays of sunlight paint the sky in shades of orange and pink, you'll witness a breathtaking panorama of Lake Batur, Mount Agung, and the surrounding volcanic landscape. It's a moment that words cannot describe.

After sunrise, explore the volcanic crater with its steaming vents and enjoy a delicious breakfast cooked using volcanic steam. The descent offers spectacular views and opportunities to spot local wildlife.`,
};

// Activity highlights
const highlights = [
    { icon: Clock, label: '6-7 Hours' },
    { icon: Footprints, label: 'Moderate' },
    { icon: Users, label: 'Max 12 pax' },
    { icon: Globe, label: 'English Guide' },
];

// What's provided
const amenities = [
    { icon: Utensils, name: 'Breakfast', description: 'Cooked on volcanic steam' },
    { icon: Droplets, name: 'Mineral Water', description: '2 bottles per person' },
    { icon: Sunrise, name: 'Flashlight', description: 'For pre-dawn hiking' },
    { icon: Shield, name: 'Travel Insurance', description: 'Basic coverage included' },
    { icon: Camera, name: 'Photo Service', description: 'Professional photos' },
    { icon: Mountain, name: 'Trekking Poles', description: 'Available on request' },
];

// Features by category
const featuresByCategory = {
    inclusion: [
        { name: 'Hotel pickup & drop-off', description: 'From Ubud, Seminyak, Kuta area' },
        { name: 'Professional local guide', description: 'Certified and experienced' },
        { name: 'Entrance fees', description: 'All permits included' },
        { name: 'Breakfast at summit', description: 'Eggs, banana, bread & coffee' },
    ],
    exclusion: [
        { name: 'Personal expenses', description: 'Tips, souvenirs, etc.' },
        { name: 'Trekking shoes', description: 'Must bring your own' },
        { name: 'Hot spring visit', description: 'Optional add-on available' },
        { name: 'Travel insurance upgrade', description: 'Premium coverage' },
    ],
    suggestion: [
        { name: 'Warm jacket', description: 'Summit can be cold (10-15°C)' },
        { name: 'Comfortable trekking shoes', description: 'Essential for rocky terrain' },
        { name: 'Small backpack', description: 'For personal items' },
        { name: 'Camera', description: 'Capture the sunrise' },
    ],
};

// Unique selling points
const uniqueFeatures = [
    { icon: Leaf, title: 'Small Groups', description: 'Max 12 people for personal experience' },
    { icon: Heart, title: 'Local Experts', description: 'Guides from Kintamani village' },
    { icon: Phone, title: 'Direct Booking', description: 'Chat with guide directly' },
    { icon: Gift, title: 'Free Photos', description: 'Professional photos included' },
];

// Activity guidelines
const activityRules = [
    { icon: Clock, text: 'Pickup: 2:00 AM - 2:30 AM' },
    { icon: Clock, text: 'Return: ~10:00 AM' },
    { icon: Users, text: 'Minimum age: 10 years' },
    { icon: ThermometerSun, text: 'Weather dependent activity' },
    { icon: Ban, text: 'Not suitable for pregnant women' },
    { icon: Ban, text: 'Not suitable for heart conditions' },
];

// Rates
const rates = [
    { id: 1, name: 'Regular', price: 450000, originalPrice: 550000, currency: 'IDR', priceType: 'person', description: 'Shared group tour', isDefault: true },
    { id: 2, name: 'Private', price: 750000, originalPrice: 900000, currency: 'IDR', priceType: 'person', description: 'Just you & your group', isDefault: false },
    { id: 3, name: 'VIP', price: 1200000, originalPrice: 1500000, currency: 'IDR', priceType: 'person', description: 'Private + hot spring', isDefault: false },
];

// Booking state
const selectedDate = ref('Jan 15, 2026');
const selectedTime = ref('2:00 AM');

// Participant selection
const adults = ref(2);
const children = ref(0);
const totalParticipants = computed(() => adults.value + children.value);
const isParticipantSelectorOpen = ref(false);

function adjustParticipants(type: 'adults' | 'children', delta: number) {
    if (type === 'adults') {
        adults.value = Math.max(1, Math.min(12, adults.value + delta));
    } else {
        children.value = Math.max(0, Math.min(12 - adults.value, children.value + delta));
    }
}

// Location info
const locationInfo = {
    address: 'Mount Batur, Kintamani',
    distance: '1.5 hours from Ubud',
    highlights: ['Active volcano', 'UNESCO Geopark', 'Sunrise viewpoint'],
};

// Guide information
const guideInfo = {
    name: 'Ketut Suwarna',
    photo: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face',
    since: '2015',
    treks: '2,500+',
    languages: ['English', 'Indonesian', 'Japanese'],
    story: 'I grew up in Trunyan village by Lake Batur. I know every path on this mountain. Guiding tourists to see the sunrise has been my passion for over 10 years.',
    whatsapp: '+6281234567890',
};

// Testimonials
const testimonials = [
    {
        name: 'Mike R.',
        country: 'USA',
        date: 'Dec 2025',
        rating: 5,
        text: 'Best sunrise I\'ve ever seen! Ketut was amazing - so knowledgeable and encouraging.',
        initials: 'MR',
    },
    {
        name: 'Emma L.',
        country: 'UK', 
        date: 'Nov 2025',
        rating: 5,
        text: 'Worth waking up at 1 AM! The volcanic breakfast was such a unique experience.',
        initials: 'EL',
    },
    {
        name: 'Yuki S.',
        country: 'Japan',
        date: 'Oct 2025', 
        rating: 5,
        text: 'Ketut-san guided us safely and the photos he took were incredible!',
        initials: 'YS',
    },
];

// Exclusive perks
const exclusivePerks = [
    { icon: Clock, title: 'Flexible Timing', description: 'Adjust pickup time for your area' },
    { icon: Gift, title: 'Free Photos', description: 'Professional quality, sent same day' },
    { icon: Zap, title: 'Small Groups', description: 'Never more than 12 people' },
    { icon: BadgeCheck, title: 'Full Refund', description: 'Cancel 24 hours before for free' },
];

// Packing tips
const packingTips = [
    'Warm jacket or fleece',
    'Comfortable trekking shoes',
    'Camera/phone',
    'Small backpack',
];

// Loyalty program
const loyaltyProgram = {
    discount: '15%',
    description: 'Book another activity',
};

// OTA savings
const otaSavings = 20;

// ============================================
// COMPONENT STATE
// ============================================

const isGalleryOpen = ref(false);
const currentImageIndex = ref(0);
const isAmenitiesOpen = ref(false);
const isFeaturesOpen = ref(false);

const isDescriptionExpanded = ref(false);
const paragraphs = computed(() => activity.description.split(/\n\n+/).filter(p => p.trim()));
const hasMultipleParagraphs = computed(() => paragraphs.value.length > 1);
const truncatedDescription = computed(() => {
    if (isDescriptionExpanded.value || !hasMultipleParagraphs.value) {
        return activity.description;
    }
    return paragraphs.value[0];
});

const selectedRateId = ref(1);
const selectedRate = computed(() => rates.find(r => r.id === selectedRateId.value) || rates[0]);

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
    <Layout title="Mount Batur Trek">

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
                                {{ activity.type }}
                            </p>
                            <h1 class="text-3xl md:text-4xl font-light text-stone-900 mb-4">
                                {{ activity.name }}
                            </h1>
                            
                            <!-- Highlights -->
                            <div class="flex flex-wrap gap-4 text-stone-600">
                                <div v-for="highlight in highlights" :key="highlight.label" class="flex items-center gap-2">
                                    <component :is="highlight.icon" class="w-5 h-5" />
                                    <span>{{ highlight.label }}</span>
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
                            <h2 class="text-xl font-medium text-stone-900">About this experience</h2>
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

                        <!-- What's Provided -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">What's provided</h2>
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
                                Show all {{ amenities.length }} items
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
                                        <Sparkles class="w-4 h-4" /> What to bring
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

                        <!-- Location -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">Meeting point</h2>
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

                        <!-- Activity Guidelines -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">Activity guidelines</h2>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <li 
                                    v-for="rule in activityRules" 
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
                                <p class="text-stone-300">Lower than Klook, GetYourGuide & Viator</p>
                            </div>
                        </div>

                        <!-- Guide Story -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <h2 class="text-xl font-medium text-stone-900">Meet your guide</h2>
                            <div class="flex items-start gap-4">
                                <img 
                                    :src="guideInfo.photo" 
                                    :alt="guideInfo.name"
                                    class="w-16 h-16 rounded-full object-cover"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-semibold text-stone-900">{{ guideInfo.name }}</p>
                                        <span class="px-2 py-0.5 bg-stone-100 text-stone-600 text-xs rounded-full">
                                            {{ guideInfo.treks }} treks
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-stone-500 mb-3">
                                        <span class="flex items-center gap-1">
                                            <Clock class="w-4 h-4" />
                                            Since {{ guideInfo.since }}
                                        </span>
                                        <span>{{ guideInfo.languages.join(', ') }}</span>
                                    </div>
                                    <p class="text-stone-600 text-sm leading-relaxed">
                                        "{{ guideInfo.story }}"
                                    </p>
                                </div>
                            </div>
                            <a 
                                :href="`https://wa.me/${guideInfo.whatsapp.replace(/\+/g, '')}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                            >
                                <MessageCircle class="w-4 h-4" />
                                Chat with Guide
                            </a>
                        </div>

                        <!-- Guest Testimonials -->
                        <div class="space-y-4 border-b border-stone-200 pb-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-medium text-stone-900">What trekkers say</h2>
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
                                    <p class="font-semibold text-stone-900">Adventure Bundle</p>
                                    <p class="text-sm text-stone-600">
                                        {{ loyaltyProgram.description }} and get <span class="font-bold text-amber-600">{{ loyaltyProgram.discount }} off</span>
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
                                        {{ formatCurrency(selectedRate.originalPrice * totalParticipants, selectedRate.currency) }}
                                    </span>
                                    <span class="text-2xl font-semibold text-stone-900">
                                        {{ formatCurrency(selectedRate.price * totalParticipants, selectedRate.currency) }}
                                    </span>
                                </p>
                                <p class="text-sm text-stone-500 mt-1">
                                    {{ totalParticipants }} participant{{ totalParticipants > 1 ? 's' : '' }} · <span class="text-green-600 font-medium">All inclusive</span>
                                </p>
                            </div>

                            <!-- Direct Booking Benefits Badge -->
                            <div class="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-lg mb-4">
                                <BadgeCheck class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                                <div>
                                    <p class="text-sm font-medium text-emerald-800">Direct Booking Benefits</p>
                                    <p class="text-xs text-emerald-600">Best rate + free photos included</p>
                                </div>
                            </div>

                            <!-- Date & Participant picker -->
                            <div class="border border-stone-300 rounded-xl overflow-hidden mb-4">
                                <div class="grid grid-cols-2 divide-x divide-stone-300">
                                    <div class="p-3 cursor-pointer hover:bg-stone-50">
                                        <p class="text-xs font-medium uppercase text-stone-700">Date</p>
                                        <p class="text-sm font-medium">{{ selectedDate }}</p>
                                    </div>
                                    <div class="p-3 cursor-pointer hover:bg-stone-50">
                                        <p class="text-xs font-medium uppercase text-stone-700">Time</p>
                                        <p class="text-sm font-medium">{{ selectedTime }}</p>
                                    </div>
                                </div>
                                <div class="border-t border-stone-300 relative">
                                    <button 
                                        @click="isParticipantSelectorOpen = !isParticipantSelectorOpen"
                                        class="w-full p-3 text-left hover:bg-stone-50 flex items-center justify-between"
                                    >
                                        <div>
                                            <p class="text-xs font-medium uppercase text-stone-700">Participants</p>
                                            <p class="text-sm font-medium">
                                                {{ adults }} adult{{ adults > 1 ? 's' : '' }}
                                                <span v-if="children > 0" class="text-stone-500">, {{ children }} child{{ children > 1 ? 'ren' : '' }}</span>
                                            </p>
                                        </div>
                                        <ChevronDown class="w-4 h-4 text-stone-400 transition-transform" :class="{ 'rotate-180': isParticipantSelectorOpen }" />
                                    </button>
                                    
                                    <!-- Participant selector dropdown -->
                                    <div 
                                        v-if="isParticipantSelectorOpen"
                                        class="absolute left-0 right-0 top-full z-20 bg-white border border-stone-200 rounded-xl shadow-lg p-4 mt-2"
                                    >
                                        <!-- Adults -->
                                        <div class="flex items-center justify-between py-3 border-b border-stone-100">
                                            <div>
                                                <p class="font-medium text-stone-900">Adults</p>
                                                <p class="text-xs text-stone-500">Age 10+</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button 
                                                    @click="adjustParticipants('adults', -1)"
                                                    :disabled="adults <= 1"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Minus class="w-3 h-3" />
                                                </button>
                                                <span class="w-6 text-center font-semibold">{{ adults }}</span>
                                                <button 
                                                    @click="adjustParticipants('adults', 1)"
                                                    :disabled="totalParticipants >= 12"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Plus class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Children -->
                                        <div class="flex items-center justify-between py-3">
                                            <div>
                                                <p class="font-medium text-stone-900">Children</p>
                                                <p class="text-xs text-stone-500">Age 10-17 (same price)</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button 
                                                    @click="adjustParticipants('children', -1)"
                                                    :disabled="children <= 0"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Minus class="w-3 h-3" />
                                                </button>
                                                <span class="w-6 text-center font-semibold">{{ children }}</span>
                                                <button 
                                                    @click="adjustParticipants('children', 1)"
                                                    :disabled="totalParticipants >= 12"
                                                    class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    <Plus class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <p class="text-xs text-stone-500 mt-3">Maximum 12 participants per group.</p>
                                        
                                        <Button 
                                            @click="isParticipantSelectorOpen = false"
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
                                <p class="text-sm font-medium text-stone-700 mb-3">Select package</p>
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
                                Book Now
                            </Button>

                            <p class="text-xs text-center text-stone-500 mt-4">
                                Free cancellation up to 24 hours before
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
                Direct Booking — Best Rate + Free Photos
            </div>
            
            <!-- Pricing Bar -->
            <div class="bg-white border-t border-stone-100 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
                <Sheet>
                    <SheetTrigger as-child>
                        <button class="w-full px-4 py-3 flex items-center justify-between">
                            <div class="text-left">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-stone-900">
                                        {{ formatCurrency(selectedRate.price * totalParticipants, selectedRate.currency) }}
                                    </span>
                                    <span class="text-xs text-stone-400 line-through">
                                        {{ formatCurrency(selectedRate.originalPrice * totalParticipants, selectedRate.currency) }}
                                    </span>
                                </div>
                                <p class="text-xs text-stone-500 mt-0.5">
                                    {{ totalParticipants }} pax · <span class="text-green-600">All inclusive</span>
                                </p>
                            </div>
                            <Button size="lg" class="bg-stone-900 hover:bg-stone-800 text-white shadow-lg flex-shrink-0">
                                Book Now
                            </Button>
                        </button>
                    </SheetTrigger>
                    <SheetContent side="bottom" class="h-[85vh] rounded-t-3xl">
                        <SheetHeader class="pb-4 border-b border-stone-200">
                            <SheetTitle class="text-xl">Booking Details</SheetTitle>
                            <SheetDescription class="sr-only">Select package and participants</SheetDescription>
                        </SheetHeader>
                        
                        <div class="py-6 space-y-6 overflow-y-auto">
                            <!-- Package Selection -->
                            <div class="space-y-3">
                                <p class="font-medium text-stone-900">Select package</p>
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

                            <!-- Date & Time -->
                            <div class="space-y-3">
                                <p class="font-medium text-stone-900">Activity details</p>
                                <div class="border border-stone-200 rounded-xl overflow-hidden">
                                    <div class="grid grid-cols-2 divide-x divide-stone-200">
                                        <div class="p-4">
                                            <p class="text-xs font-medium uppercase text-stone-500">Date</p>
                                            <p class="font-medium">{{ selectedDate }}</p>
                                        </div>
                                        <div class="p-4">
                                            <p class="text-xs font-medium uppercase text-stone-500">Time</p>
                                            <p class="font-medium">{{ selectedTime }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Participant selector inline -->
                                <div class="border border-stone-200 rounded-xl p-4 space-y-4">
                                    <p class="font-medium text-stone-900">Participants</p>
                                    
                                    <!-- Adults -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-stone-900">Adults</p>
                                            <p class="text-xs text-stone-500">Age 10+</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button 
                                                @click="adjustParticipants('adults', -1)"
                                                :disabled="adults <= 1"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </button>
                                            <span class="w-6 text-center font-semibold">{{ adults }}</span>
                                            <button 
                                                @click="adjustParticipants('adults', 1)"
                                                :disabled="totalParticipants >= 12"
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
                                            <p class="text-xs text-stone-500">Age 10-17</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button 
                                                @click="adjustParticipants('children', -1)"
                                                :disabled="children <= 0"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </button>
                                            <span class="w-6 text-center font-semibold">{{ children }}</span>
                                            <button 
                                                @click="adjustParticipants('children', 1)"
                                                :disabled="totalParticipants >= 12"
                                                class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center hover:border-stone-500 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-stone-500">Max 12 participants per group.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom CTA -->
                        <div class="pt-4 border-t border-stone-200">
                            <Button size="lg" class="w-full bg-stone-900 hover:bg-stone-800 text-white">
                                Book Now
                            </Button>
                            <p class="text-xs text-center text-stone-500 mt-3">Free cancellation up to 24 hours before</p>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </div>

        <!-- Spacer for mobile fixed bar -->
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

        <!-- What's Provided Dialog -->
        <Dialog v-model:open="isAmenitiesOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>What's provided</DialogTitle>
                    <DialogDescription>Everything included in your trek</DialogDescription>
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

        <!-- Features Dialog -->
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
