<script setup lang="ts">
import { ref } from 'vue';
import Layout from './Layout.vue';
import { Button } from '@/components/ui/button';
import { 
    Check, 
    Calendar,
    MapPin,
    Users,
    Phone,
    Mail,
    Clock,
    Download,
    Share2,
    MessageCircle,
    Copy,
    CheckCircle,
    CreditCard,
    Home
} from 'lucide-vue-next';

// ============================================
// PAGE METADATA
// ============================================
defineOptions({
    layout: Layout,
});

// ============================================
// HARDCODED BOOKING DATA FOR PREVIEW
// ============================================

const booking = ref({
    code: 'LBC-M4X8K2J',
    status: 'confirmed', // pending, confirmed, completed, cancelled
    createdAt: '2026-01-10T02:30:00',
    
    property: {
        name: 'Lake View Cabin',
        type: 'Entire cabin',
        location: 'Songan A, Kintamani, Bangli, Bali',
        image: 'https://images.unsplash.com/photo-1587061949409-02df41d5e562?w=600&h=400&fit=crop',
    },
    
    dates: {
        checkIn: '2026-01-15',
        checkOut: '2026-01-17',
        nights: 2,
    },
    
    guests: {
        adults: 2,
        children: 0,
        infants: 0,
        total: 2,
    },
    
    guestInfo: {
        name: 'John Doe',
        email: 'john@example.com',
        phone: '+62 812 3456 7890',
        specialRequests: 'Early check-in if possible',
    },
    
    payment: {
        method: 'Bank Transfer',
        status: 'paid', // pending, paid
        subtotal: 3000000,
        cleaningFee: 200000,
        serviceFee: 150000,
        total: 3350000,
        currency: 'IDR',
    },
    
    host: {
        name: 'Made Wirawan',
        phone: '+62 812 9876 5432',
        whatsapp: '+6281298765432',
    },
});

const copied = ref(false);

// ============================================
// METHODS
// ============================================

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatShortDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
}

function copyBookingCode() {
    navigator.clipboard.writeText(booking.value.code);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

function getStatusColor(status: string) {
    switch (status) {
        case 'confirmed': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'completed': return 'bg-blue-100 text-blue-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-stone-100 text-stone-800';
    }
}

function getStatusLabel(status: string) {
    switch (status) {
        case 'confirmed': return 'Confirmed';
        case 'pending': return 'Pending Payment';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return status;
    }
}
</script>

<template>
    <div class="min-h-screen bg-stone-50">
        <!-- Header -->
        <header class="bg-white border-b border-stone-200">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <a href="/" class="flex items-center gap-2 text-stone-600 hover:text-stone-900">
                        <Home class="w-5 h-5" />
                        <span>Back to Home</span>
                    </a>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm">
                            <Download class="w-4 h-4 mr-2" />
                            Download
                        </Button>
                        <Button variant="outline" size="sm">
                            <Share2 class="w-4 h-4 mr-2" />
                            Share
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 py-8">
            <!-- Success Banner -->
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-8">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <CheckCircle class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-green-800 mb-1">Booking Confirmed!</h1>
                        <p class="text-green-700">Your reservation has been confirmed. We've sent a confirmation email to {{ booking.guestInfo.email }}</p>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column: Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Booking Code -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-stone-900">Booking Details</h2>
                            <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusColor(booking.status)]">
                                {{ getStatusLabel(booking.status) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-stone-50 rounded-xl mb-6">
                            <div>
                                <p class="text-sm text-stone-500">Booking Code</p>
                                <p class="text-2xl font-mono font-bold text-stone-900">{{ booking.code }}</p>
                            </div>
                            <Button variant="outline" size="sm" @click="copyBookingCode">
                                <Copy v-if="!copied" class="w-4 h-4 mr-2" />
                                <Check v-else class="w-4 h-4 mr-2 text-green-600" />
                                {{ copied ? 'Copied!' : 'Copy' }}
                            </Button>
                        </div>
                        
                        <!-- Property -->
                        <div class="flex gap-4 pb-6 border-b border-stone-100">
                            <img 
                                :src="booking.property.image" 
                                :alt="booking.property.name" 
                                class="w-28 h-24 rounded-xl object-cover"
                            />
                            <div>
                                <p class="text-sm text-stone-500">{{ booking.property.type }}</p>
                                <h3 class="text-lg font-semibold text-stone-900 mb-1">{{ booking.property.name }}</h3>
                                <p class="text-sm text-stone-500 flex items-center gap-1">
                                    <MapPin class="w-4 h-4" />
                                    {{ booking.property.location }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Dates & Guests -->
                        <div class="grid grid-cols-2 gap-6 py-6 border-b border-stone-100">
                            <div>
                                <div class="flex items-center gap-2 text-stone-500 mb-2">
                                    <Calendar class="w-4 h-4" />
                                    <span class="text-sm font-medium">Check-in</span>
                                </div>
                                <p class="font-semibold text-stone-900">{{ formatShortDate(booking.dates.checkIn) }}</p>
                                <p class="text-sm text-stone-500">From 2:00 PM</p>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 text-stone-500 mb-2">
                                    <Calendar class="w-4 h-4" />
                                    <span class="text-sm font-medium">Check-out</span>
                                </div>
                                <p class="font-semibold text-stone-900">{{ formatShortDate(booking.dates.checkOut) }}</p>
                                <p class="text-sm text-stone-500">Before 11:00 AM</p>
                            </div>
                        </div>
                        
                        <div class="py-6 border-b border-stone-100">
                            <div class="flex items-center gap-2 text-stone-500 mb-2">
                                <Users class="w-4 h-4" />
                                <span class="text-sm font-medium">Guests</span>
                            </div>
                            <p class="font-semibold text-stone-900">
                                {{ booking.guests.adults }} adult{{ booking.guests.adults > 1 ? 's' : '' }}
                                <span v-if="booking.guests.children > 0">, {{ booking.guests.children }} child{{ booking.guests.children > 1 ? 'ren' : '' }}</span>
                                <span v-if="booking.guests.infants > 0">, {{ booking.guests.infants }} infant{{ booking.guests.infants > 1 ? 's' : '' }}</span>
                            </p>
                            <p class="text-sm text-stone-500">{{ booking.dates.nights }} night{{ booking.dates.nights > 1 ? 's' : '' }}</p>
                        </div>
                        
                        <!-- Guest Info -->
                        <div class="py-6">
                            <h3 class="font-medium text-stone-900 mb-4">Guest Information</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-stone-100 rounded-full flex items-center justify-center">
                                        <Users class="w-5 h-5 text-stone-500" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ booking.guestInfo.name }}</p>
                                        <p class="text-sm text-stone-500">Primary Guest</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-stone-100 rounded-full flex items-center justify-center">
                                        <Mail class="w-5 h-5 text-stone-500" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ booking.guestInfo.email }}</p>
                                        <p class="text-sm text-stone-500">Email</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-stone-100 rounded-full flex items-center justify-center">
                                        <Phone class="w-5 h-5 text-stone-500" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ booking.guestInfo.phone }}</p>
                                        <p class="text-sm text-stone-500">Phone</p>
                                    </div>
                                </div>
                                <div v-if="booking.guestInfo.specialRequests" class="p-4 bg-stone-50 rounded-xl">
                                    <p class="text-sm text-stone-500 mb-1">Special Requests</p>
                                    <p class="text-stone-900">{{ booking.guestInfo.specialRequests }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Details -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-stone-900 mb-4">Payment Details</h2>
                        
                        <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl mb-6">
                            <CreditCard class="w-5 h-5 text-green-600" />
                            <div>
                                <p class="font-medium text-green-800">{{ booking.payment.method }}</p>
                                <p class="text-sm text-green-700">Payment received</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-stone-600">Accommodation ({{ booking.dates.nights }} nights)</span>
                                <span>{{ formatCurrency(booking.payment.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-600">Cleaning fee</span>
                                <span>{{ formatCurrency(booking.payment.cleaningFee) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-600">Service fee</span>
                                <span>{{ formatCurrency(booking.payment.serviceFee) }}</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-stone-200 text-base font-bold">
                                <span>Total</span>
                                <span>{{ formatCurrency(booking.payment.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Host & Actions -->
                <div class="space-y-6">
                    <!-- Host Contact -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="font-semibold text-stone-900 mb-4">Your Host</h3>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 bg-stone-200 rounded-full flex items-center justify-center text-xl font-bold text-stone-600">
                                {{ booking.host.name.split(' ').map(n => n[0]).join('') }}
                            </div>
                            <div>
                                <p class="font-semibold text-stone-900">{{ booking.host.name }}</p>
                                <p class="text-sm text-stone-500">Property Host</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <a 
                                :href="`tel:${booking.host.phone}`" 
                                class="flex items-center gap-3 p-3 border border-stone-200 rounded-xl hover:bg-stone-50 transition-colors"
                            >
                                <Phone class="w-5 h-5 text-stone-500" />
                                <span class="text-stone-900">{{ booking.host.phone }}</span>
                            </a>
                            <a 
                                :href="`https://wa.me/${booking.host.whatsapp.replace(/\+/g, '')}`"
                                target="_blank"
                                class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 transition-colors"
                            >
                                <MessageCircle class="w-5 h-5 text-green-600" />
                                <span class="text-green-800 font-medium">Chat on WhatsApp</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Important Info -->
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
                        <h3 class="font-semibold text-amber-900 mb-3">Important Information</h3>
                        <ul class="space-y-2 text-sm text-amber-800">
                            <li class="flex items-start gap-2">
                                <Clock class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                <span>Check-in from 2:00 PM, check-out by 11:00 AM</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Check class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                <span>Show this booking code upon arrival</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Phone class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                <span>Contact host 24 hours before arrival</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Actions -->
                    <div class="space-y-3">
                        <Button variant="outline" class="w-full">
                            Modify Booking
                        </Button>
                        <Button variant="ghost" class="w-full text-red-600 hover:text-red-700 hover:bg-red-50">
                            Cancel Booking
                        </Button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
