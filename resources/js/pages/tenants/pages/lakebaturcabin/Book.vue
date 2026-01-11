<script setup lang="ts">
import { computed, ref, nextTick } from 'vue';
import { useMediaQuery } from '@vueuse/core';
import Layout from './Layout.vue';
import BookingNavbar from '@/components/tenants/default/BookingNavbar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Check, 
    ChevronLeft,
    User,
    CreditCard,
    Star,
    Shield,
    ClipboardList,
    PartyPopper
} from 'lucide-vue-next';

// ============================================
// PAGE METADATA
// ============================================
defineOptions({
    layout: Layout,
});

// ============================================
// RESPONSIVE DETECTION
// ============================================
const isDesktop = useMediaQuery('(min-width: 1024px)');

// ============================================
// HARDCODED DATA FOR PREVIEW
// ============================================

const property = {
    name: 'Lake View Cabin | Private Pool & Kitchen',
    type: 'Entire cabin',
    location: 'Kintamani, Bali',
    image: 'https://images.unsplash.com/photo-1587061949409-02df41d5e562?w=400&h=300&fit=crop',
    rating: 5.0,
    reviews: 6,
    pricePerNight: 1500000,
    originalPrice: 1800000,
    cleaningFee: 200000,
    serviceFee: 150000,
    currency: 'IDR',
};

// Booking state
const currentStep = ref(1);
const totalSteps = 4;

// Dates
const checkInDate = ref('2026-02-01');
const checkOutDate = ref('2026-02-02');
const nights = computed(() => {
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    return Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));
});

// Guests
const adults = ref(1);
const children = ref(0);
const infants = ref(0);

// Guest Info
const guestInfo = ref({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    countryCode: '+62',
    specialRequests: '',
});

// Payment
const cardInfo = ref({
    number: '',
    expiry: '',
    cvv: '',
    name: '',
});
const agreedToTerms = ref(false);

// Transaction state
const isProcessing = ref(false);
const isCompleted = ref(false);
const bookingCode = ref('');

// Step transition state
const isTransitioning = ref(false);

// ============================================
// COMPUTED
// ============================================

const subtotal = computed(() => property.pricePerNight * nights.value);
const totalPrice = computed(() => subtotal.value + property.cleaningFee + property.serviceFee);

// Steps: 1-Summary, 2-Info, 3-Payment, 4-Confirmed
const steps = [
    { number: 1, title: 'Confirm Summary', icon: ClipboardList },
    { number: 2, title: 'Personal Info', icon: User },
    { number: 3, title: 'Credit Card', icon: CreditCard },
    { number: 4, title: 'Confirmed', icon: PartyPopper },
];

const canProceedStep = (step: number) => {
    switch (step) {
        case 1:
            return true; // Summary review
        case 2:
            return guestInfo.value.firstName && guestInfo.value.lastName && 
                   guestInfo.value.email && guestInfo.value.phone;
        case 3:
            return cardInfo.value.number && cardInfo.value.expiry && 
                   cardInfo.value.cvv && cardInfo.value.name && agreedToTerms.value;
        case 4:
            return true;
        default:
            return false;
    }
};

const canProceed = computed(() => canProceedStep(currentStep.value));

const guestSummary = computed(() => {
    let text = `${adults.value} adult${adults.value > 1 ? 's' : ''}`;
    if (children.value > 0) {
        text += `, ${children.value} child${children.value > 1 ? 'ren' : ''}`;
    }
    if (infants.value > 0) {
        text += `, ${infants.value} infant${infants.value > 1 ? 's' : ''}`;
    }
    return text;
});

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

function formatDateRange(): string {
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    const opts: Intl.DateTimeFormatOptions = { month: 'short', day: 'numeric' };
    return `${start.toLocaleDateString('en-US', opts)} – ${end.toLocaleDateString('en-US', opts)}, ${end.getFullYear()}`;
}

function nextStep() {
    if (currentStep.value < totalSteps && canProceed.value) {
        transitionToStep(currentStep.value + 1);
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        transitionToStep(currentStep.value - 1);
    }
}

function handleClose() {
    window.history.back();
}

function transitionToStep(targetStep: number) {
    isTransitioning.value = true;
    
    setTimeout(() => {
        currentStep.value = targetStep;
        window.scrollTo({ top: 0, behavior: 'instant' });
        
        nextTick(() => {
            isTransitioning.value = false;
        });
    }, 150);
}

async function submitBooking() {
    isProcessing.value = true;
    
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    bookingCode.value = 'LBC-' + Date.now().toString(36).toUpperCase();
    
    isProcessing.value = false;
    isCompleted.value = true;
    currentStep.value = 4;
}

const allSectionsComplete = computed(() => {
    return canProceedStep(1) && canProceedStep(2) && canProceedStep(3);
});
</script>

<template>
    <div class="min-h-screen bg-white lg:bg-stone-50">
        <!-- SUCCESS STATE -->
        <div v-if="isCompleted" class="max-w-lg mx-auto px-6 py-12">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Check class="w-10 h-10 text-green-600" />
                </div>
                <h1 class="text-2xl font-bold text-stone-900 mb-2">You're all set!</h1>
                <p class="text-stone-500">Your booking has been confirmed.</p>
            </div>
            
            <div class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
                <div class="flex items-center gap-4 mb-4 pb-4 border-b border-stone-100">
                    <img :src="property.image" :alt="property.name" class="w-16 h-16 rounded-lg object-cover" />
                    <div>
                        <h3 class="font-semibold text-stone-900 text-sm">{{ property.name }}</h3>
                        <p class="text-xs text-stone-500">{{ property.location }}</p>
                    </div>
                </div>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-stone-500">Booking Code</span>
                        <span class="font-mono font-bold text-stone-900">{{ bookingCode }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Dates</span>
                        <span class="font-medium">{{ formatDateRange() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Guests</span>
                        <span class="font-medium">{{ guestSummary }}</span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-stone-200">
                        <span class="font-semibold">Total Paid</span>
                        <span class="font-bold">{{ formatCurrency(totalPrice) }}</span>
                    </div>
                </div>
            </div>
            
            <p class="text-sm text-stone-500 text-center mb-6">
                Confirmation sent to <strong>{{ guestInfo.email }}</strong>
            </p>
            
            <div class="flex gap-3">
                <Button variant="outline" class="flex-1" as="a" href="/">Home</Button>
                <Button class="flex-1 bg-stone-900 hover:bg-stone-800">View Booking</Button>
            </div>
        </div>

        <!-- BOOKING FLOW -->
        <div v-else>
            <!-- ==================== MOBILE LAYOUT ==================== -->
            <div v-if="!isDesktop" class="max-w-lg mx-auto bg-white min-h-screen relative">
                <BookingNavbar 
                    :current-step="currentStep"
                    :steps="steps"
                    :show-back-button="currentStep > 1"
                    @back="prevStep"
                    @close="handleClose"
                />

                <main 
                    class="pb-32 transition-opacity duration-150"
                    :class="isTransitioning ? 'opacity-0' : 'opacity-100'"
                >
                    <!-- Step 1: Confirm Summary -->
                    <div v-if="currentStep === 1" class="px-6 py-6">
                        <h1 class="text-2xl font-semibold text-stone-900 mb-6">Confirm your booking</h1>
                        
                        <!-- Property Card -->
                        <div class="border border-stone-200 rounded-xl p-4 mb-6">
                            <div class="flex gap-4">
                                <img :src="property.image" :alt="property.name" class="w-20 h-16 rounded-lg object-cover" />
                                <div>
                                    <h3 class="font-semibold text-stone-900 text-sm">{{ property.name }}</h3>
                                    <div class="flex items-center gap-1 text-sm text-stone-500 mt-1">
                                        <Star class="w-3.5 h-3.5 fill-current text-stone-900" />
                                        <span>{{ property.rating }}</span>
                                        <span class="text-emerald-600 font-medium ml-1">Top Rated</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details -->
                        <div class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-stone-100">
                                <div>
                                    <p class="font-medium text-stone-900">Dates</p>
                                    <p class="text-sm text-stone-500">{{ formatDateRange() }}</p>
                                </div>
                                <Button variant="outline" size="sm">Change</Button>
                            </div>
                            <div class="flex justify-between py-3 border-b border-stone-100">
                                <div>
                                    <p class="font-medium text-stone-900">Guests</p>
                                    <p class="text-sm text-stone-500">{{ guestSummary }}</p>
                                </div>
                                <Button variant="outline" size="sm">Change</Button>
                            </div>
                            <div class="py-3">
                                <p class="font-medium text-stone-900 mb-2">Price details</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between text-stone-600">
                                        <span>{{ formatCurrency(property.pricePerNight) }} × {{ nights }} night</span>
                                        <span>{{ formatCurrency(subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-stone-600">
                                        <span>Cleaning fee</span>
                                        <span>{{ formatCurrency(property.cleaningFee) }}</span>
                                    </div>
                                    <div class="flex justify-between text-stone-600">
                                        <span>Service fee</span>
                                        <span>{{ formatCurrency(property.serviceFee) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-3 border-t border-stone-200 font-semibold">
                                        <span>Total (IDR)</span>
                                        <span>{{ formatCurrency(totalPrice) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Personal Info -->
                    <div v-else-if="currentStep === 2" class="px-6 py-6">
                        <h1 class="text-2xl font-semibold text-stone-900 mb-2">Your information</h1>
                        <p class="text-sm text-stone-500 mb-6">We'll use this to send your confirmation</p>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">First name</Label>
                                    <Input v-model="guestInfo.firstName" placeholder="John" class="mt-1.5" />
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Last name</Label>
                                    <Input v-model="guestInfo.lastName" placeholder="Doe" class="mt-1.5" />
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Email</Label>
                                <Input v-model="guestInfo.email" type="email" placeholder="john@example.com" class="mt-1.5" />
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Phone number</Label>
                                <div class="flex gap-2 mt-1.5">
                                    <select v-model="guestInfo.countryCode" class="w-20 px-3 py-2 border border-stone-200 rounded-lg text-sm bg-white">
                                        <option value="+62">+62</option>
                                        <option value="+1">+1</option>
                                    </select>
                                    <Input v-model="guestInfo.phone" type="tel" placeholder="812 3456 7890" class="flex-1" />
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Special requests (optional)</Label>
                                <Textarea v-model="guestInfo.specialRequests" placeholder="Any special requirements?" class="mt-1.5" rows="3" />
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Credit Card -->
                    <div v-else-if="currentStep === 3" class="px-6 py-6">
                        <h1 class="text-2xl font-semibold text-stone-900 mb-6">Payment</h1>
                        
                        <div class="space-y-4">
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Card number</Label>
                                <Input v-model="cardInfo.number" placeholder="1234 5678 9012 3456" class="mt-1.5" maxlength="19" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Expiry date</Label>
                                    <Input v-model="cardInfo.expiry" placeholder="MM / YY" class="mt-1.5" maxlength="7" />
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">CVV</Label>
                                    <Input v-model="cardInfo.cvv" placeholder="123" class="mt-1.5" maxlength="4" type="password" />
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Cardholder name</Label>
                                <Input v-model="cardInfo.name" placeholder="Name on card" class="mt-1.5" />
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                            <Shield class="w-6 h-6 text-green-600 flex-shrink-0" />
                            <div>
                                <p class="font-medium text-green-800">Secure Payment</p>
                                <p class="text-sm text-green-700">Your card info is encrypted.</p>
                            </div>
                        </div>
                        
                        <!-- Terms -->
                        <label class="flex items-start gap-3 cursor-pointer mt-6">
                            <input type="checkbox" v-model="agreedToTerms" class="w-5 h-5 mt-0.5 rounded accent-stone-900" />
                            <span class="text-sm text-stone-600">
                                I agree to the <a href="#" class="underline">Terms of Service</a> and 
                                <a href="#" class="underline">Cancellation Policy</a>.
                            </span>
                        </label>
                    </div>
                </main>

                <!-- Mobile CTA -->
                <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-stone-200 p-4">
                    <Button 
                        v-if="currentStep < 3"
                        @click="nextStep"
                        :disabled="!canProceed"
                        class="w-full h-12 bg-stone-900 hover:bg-stone-800 text-white text-base font-semibold rounded-xl"
                    >
                        Next
                    </Button>
                    <Button 
                        v-else
                        @click="submitBooking"
                        :disabled="!canProceed || isProcessing"
                        class="w-full h-12 bg-emerald-600 hover:bg-emerald-700 text-white text-base font-semibold rounded-xl"
                    >
                        <span v-if="isProcessing">Processing...</span>
                        <span v-else>Confirm and Pay</span>
                    </Button>
                </div>
            </div>

            <!-- ==================== DESKTOP LAYOUT ==================== -->
            <div v-else class="max-w-6xl mx-auto px-6 py-8">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-8">
                    <button @click="handleClose" class="p-2 hover:bg-stone-100 rounded-full">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    <h1 class="text-3xl font-semibold text-stone-900">Confirm and pay</h1>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-[1fr_380px] gap-12">
                    <!-- LEFT: Form Sections (always visible) -->
                    <div class="space-y-6">
                        <!-- Section 1: Your Trip -->
                        <div class="bg-white rounded-xl border border-stone-200 p-6">
                            <h2 class="text-xl font-semibold text-stone-900 mb-6">Your trip</h2>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-stone-100">
                                    <div>
                                        <p class="font-medium text-stone-900">Dates</p>
                                        <p class="text-sm text-stone-500">{{ formatDateRange() }}</p>
                                    </div>
                                    <button class="text-sm font-medium underline">Edit</button>
                                </div>
                                <div class="flex justify-between py-3">
                                    <div>
                                        <p class="font-medium text-stone-900">Guests</p>
                                        <p class="text-sm text-stone-500">{{ guestSummary }}</p>
                                    </div>
                                    <button class="text-sm font-medium underline">Edit</button>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Your Information -->
                        <div class="bg-white rounded-xl border border-stone-200 p-6">
                            <h2 class="text-xl font-semibold text-stone-900 mb-6">Your information</h2>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label class="text-sm font-medium text-stone-700">First name</Label>
                                        <Input v-model="guestInfo.firstName" placeholder="John" class="mt-1.5" />
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-stone-700">Last name</Label>
                                        <Input v-model="guestInfo.lastName" placeholder="Doe" class="mt-1.5" />
                                    </div>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Email</Label>
                                    <Input v-model="guestInfo.email" type="email" placeholder="john@example.com" class="mt-1.5" />
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Phone number</Label>
                                    <div class="flex gap-2 mt-1.5">
                                        <select v-model="guestInfo.countryCode" class="w-20 px-3 py-2 border border-stone-200 rounded-lg text-sm bg-white">
                                            <option value="+62">+62</option>
                                            <option value="+1">+1</option>
                                        </select>
                                        <Input v-model="guestInfo.phone" type="tel" placeholder="812 3456 7890" class="flex-1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Payment Method -->
                        <div class="bg-white rounded-xl border border-stone-200 p-6">
                            <h2 class="text-xl font-semibold text-stone-900 mb-6">Pay with</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Card number</Label>
                                    <Input v-model="cardInfo.number" placeholder="1234 5678 9012 3456" class="mt-1.5" maxlength="19" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label class="text-sm font-medium text-stone-700">Expiry</Label>
                                        <Input v-model="cardInfo.expiry" placeholder="MM / YY" class="mt-1.5" maxlength="7" />
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-stone-700">CVV</Label>
                                        <Input v-model="cardInfo.cvv" placeholder="123" class="mt-1.5" maxlength="4" type="password" />
                                    </div>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Name on card</Label>
                                    <Input v-model="cardInfo.name" placeholder="Full name" class="mt-1.5" />
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Submit -->
                        <div class="space-y-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" v-model="agreedToTerms" class="w-5 h-5 mt-0.5 rounded accent-stone-900" />
                                <span class="text-sm text-stone-600">
                                    By selecting the button below, I agree to the 
                                    <a href="#" class="underline">House Rules</a>, 
                                    <a href="#" class="underline">Cancellation Policy</a>, and the 
                                    <a href="#" class="underline">Guest Refund Policy</a>.
                                </span>
                            </label>
                            
                            <Button 
                                @click="submitBooking"
                                :disabled="!allSectionsComplete || isProcessing"
                                class="w-full h-14 bg-emerald-600 hover:bg-emerald-700 text-white text-lg font-semibold rounded-xl"
                            >
                                <span v-if="isProcessing">Processing...</span>
                                <span v-else>Confirm and pay</span>
                            </Button>
                        </div>
                    </div>

                    <!-- RIGHT: Sticky Summary -->
                    <div class="relative">
                        <div class="sticky top-8">
                            <div class="bg-white rounded-xl border border-stone-200 p-6">
                                <!-- Property -->
                                <div class="flex gap-4 pb-6 border-b border-stone-100">
                                    <img :src="property.image" :alt="property.name" class="w-28 h-24 rounded-lg object-cover" />
                                    <div>
                                        <h3 class="font-semibold text-stone-900 leading-tight">{{ property.name }}</h3>
                                        <div class="flex items-center gap-1 mt-1 text-sm text-stone-500">
                                            <Star class="w-3.5 h-3.5 fill-current text-stone-900" />
                                            <span>{{ property.rating }} ({{ property.reviews }})</span>
                                            <span class="mx-1">·</span>
                                            <span class="text-emerald-600 font-medium">Top Rated</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Free Cancellation -->
                                <div class="py-4 border-b border-stone-100">
                                    <p class="font-medium text-stone-900">Free cancellation</p>
                                    <p class="text-sm text-stone-500">Cancel within 24 hours for full refund.</p>
                                </div>
                                
                                <!-- Dates -->
                                <div class="py-4 border-b border-stone-100 flex justify-between">
                                    <div>
                                        <p class="font-medium text-stone-900">Dates</p>
                                        <p class="text-sm text-stone-500">{{ formatDateRange() }}</p>
                                    </div>
                                    <button class="text-sm font-medium underline">Change</button>
                                </div>
                                
                                <!-- Guests -->
                                <div class="py-4 border-b border-stone-100 flex justify-between">
                                    <div>
                                        <p class="font-medium text-stone-900">Guests</p>
                                        <p class="text-sm text-stone-500">{{ guestSummary }}</p>
                                    </div>
                                    <button class="text-sm font-medium underline">Change</button>
                                </div>
                                
                                <!-- Price Details -->
                                <div class="py-4 border-b border-stone-100">
                                    <p class="font-medium text-stone-900 mb-3">Price details</p>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between text-stone-600">
                                            <span>{{ nights }} night × {{ formatCurrency(property.pricePerNight) }}</span>
                                            <span>{{ formatCurrency(subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between text-stone-600">
                                            <span>Cleaning fee</span>
                                            <span>{{ formatCurrency(property.cleaningFee) }}</span>
                                        </div>
                                        <div class="flex justify-between text-stone-600">
                                            <span>Service fee</span>
                                            <span>{{ formatCurrency(property.serviceFee) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Total -->
                                <div class="pt-4 flex justify-between font-semibold text-lg">
                                    <span>Total <span class="underline text-sm font-normal">IDR</span></span>
                                    <span>{{ formatCurrency(totalPrice) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
