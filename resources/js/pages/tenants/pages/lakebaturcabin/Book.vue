<script setup lang="ts">
import { computed, ref, nextTick } from 'vue';
import Layout from './Layout.vue';
import BookingNavbar from '@/components/tenants/default/BookingNavbar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Check, 
    User,
    CreditCard,
    Star,
    Shield,
    ClipboardList,
    PartyPopper
} from 'lucide-vue-next';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetTrigger
} from '@/components/ui/sheet';

// ============================================
// PAGE METADATA
// ============================================
defineOptions({
    layout: Layout,
});

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

// Dates (hardcoded for now)
const checkInDate = ref('2026-02-01');
const checkOutDate = ref('2026-02-02');
const nights = computed(() => {
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    return Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));
});

// Guests (passed from ProductDetail - hardcoded for now)
const adults = ref(1);
const children = ref(0);
const infants = ref(0);
const totalGuests = computed(() => adults.value + children.value);

// Guest Info
const guestInfo = ref({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    countryCode: '+62',
    specialRequests: '',
});

// Payment - Credit Card
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
const mainContent = ref<HTMLElement | null>(null);

// ============================================
// COMPUTED
// ============================================

const subtotal = computed(() => property.pricePerNight * nights.value);
const totalPrice = computed(() => subtotal.value + property.cleaningFee + property.serviceFee);

const steps = [
    { number: 1, title: 'Confirm Summary', icon: ClipboardList },
    { number: 2, title: 'Personal Info', icon: User },
    { number: 3, title: 'Payment', icon: CreditCard },
    { number: 4, title: 'Confirmed', icon: PartyPopper },
];

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return true; // Summary review, always can proceed
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
});

const stepTitles: Record<number, string> = {
    1: 'Confirm your booking',
    2: 'Your information',
    3: 'Payment details',
    4: 'Booking confirmed',
};

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
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

function formatDateRange(): string {
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    const opts: Intl.DateTimeFormatOptions = { month: 'short', day: 'numeric', year: 'numeric' };
    return `${start.toLocaleDateString('en-US', opts).replace(',', '')} – ${end.toLocaleDateString('en-US', { day: 'numeric' })}, ${end.getFullYear()}`;
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

// Simple step transition with fade effect (no URL sync - like Airbnb)
function transitionToStep(targetStep: number) {
    // Security: Validate target step is accessible
    if (!canAccessStep(targetStep) || targetStep < 1 || targetStep > totalSteps) {
        console.warn(`Step ${targetStep} is not accessible`);
        return;
    }
    
    isTransitioning.value = true;
    
    // Short delay for fade-out effect
    setTimeout(() => {
        currentStep.value = targetStep;
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'instant' });
        
        // Fade back in
        nextTick(() => {
            isTransitioning.value = false;
        });
    }, 150);
}

// ============================================
// STEP SECURITY & VALIDATION
// ============================================

// Check if user can access a specific step (must have completed all previous steps)
function canAccessStep(targetStep: number): boolean {
    // Step 1 is always accessible
    if (targetStep === 1) return true;
    
    // Step 4 (confirmed) is only accessible after successful booking
    if (targetStep === 4) return isCompleted.value;
    
    // For steps 2-3, check if previous step requirements are met
    switch (targetStep) {
        case 2: // Info - need to have viewed summary
            return true;
        case 3: // Payment - need guest info filled
            return !!(guestInfo.value.firstName && guestInfo.value.lastName && 
                   guestInfo.value.email && guestInfo.value.phone);
        default:
            return false;
    }
}

async function submitBooking() {
    isProcessing.value = true;
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Generate booking code
    bookingCode.value = 'LBC-' + Date.now().toString(36).toUpperCase();
    
    isProcessing.value = false;
    isCompleted.value = true;
}
</script>

<template>
    <div class="min-h-screen bg-stone-50">
        <!-- Success State -->
        <div v-if="isCompleted" class="min-h-screen flex items-center justify-center p-4">
            <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Check class="w-10 h-10 text-green-600" />
                </div>
                <h1 class="text-2xl font-bold text-stone-900 mb-2">Booking Confirmed!</h1>
                <p class="text-stone-500 mb-6">Your reservation has been successfully placed.</p>
                
                <div class="bg-stone-50 rounded-xl p-6 mb-6 text-left">
                    <div class="flex items-center gap-4 mb-4">
                        <img :src="property.image" :alt="property.name" class="w-20 h-20 rounded-lg object-cover" />
                        <div>
                            <h3 class="font-semibold text-stone-900">{{ property.name }}</h3>
                            <p class="text-sm text-stone-500">{{ property.location }}</p>
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
                            <span class="font-semibold text-stone-900">Total Paid</span>
                            <span class="font-bold text-stone-900">{{ formatCurrency(totalPrice) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <Button variant="outline" class="flex-1" as="a" href="/">
                        Back to Home
                    </Button>
                    <Button class="flex-1 bg-stone-900 hover:bg-stone-800">
                        View Booking
                    </Button>
                </div>
            </div>
        </div>

        <!-- Booking Flow -->
        <div v-else class="max-w-lg mx-auto bg-white min-h-screen relative">
            <!-- Booking Navbar -->
            <BookingNavbar 
                :current-step="currentStep"
                :steps="steps"
                :show-back-button="currentStep > 1"
                @back="prevStep"
                @close="handleClose"
            />

            <!-- Main Content with transition -->
            <main 
                ref="mainContent"
                class="pb-32 md:pb-8 transition-opacity duration-150"
                :class="isTransitioning ? 'opacity-0' : 'opacity-100'"
            >
                <!-- Page Title -->
                <div class="px-6 pt-6 pb-4">
                    <h1 class="text-2xl font-semibold text-stone-900">
                        {{ stepTitles[currentStep] }}
                    </h1>
                </div>

                <!-- Step 1: Confirm Summary -->
                <div v-if="currentStep === 1">

                <!-- Booking Summary Card (shared between steps) -->
                <div class="mx-6 mb-6 border border-stone-200 rounded-xl overflow-hidden">
                    <!-- Property Info -->
                    <div class="p-4 flex gap-4">
                        <img 
                            :src="property.image" 
                            :alt="property.name" 
                            class="w-24 h-20 rounded-lg object-cover flex-shrink-0"
                        />
                        <div class="min-w-0">
                            <h3 class="font-semibold text-stone-900 leading-tight">{{ property.name }}</h3>
                            <div class="flex items-center gap-1 mt-1 text-sm text-stone-600">
                                <Star class="w-3.5 h-3.5 fill-current" />
                                <span>{{ property.rating }} ({{ property.reviews }})</span>
                                <span class="mx-1">·</span>
                                <span class="text-emerald-600 font-medium">Top Rated</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dates Row -->
                    <div class="px-4 py-4 border-t border-stone-100 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-stone-900">Dates</p>
                            <p class="text-sm text-stone-500">{{ formatDateRange() }}</p>
                        </div>
                        <Button variant="outline" size="sm" class="rounded-lg h-9">
                            Change
                        </Button>
                    </div>

                    <!-- Guests Row -->
                    <div class="px-4 py-4 border-t border-stone-100 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-stone-900">Guests</p>
                            <p class="text-sm text-stone-500">{{ guestSummary }}</p>
                        </div>
                        <Button variant="outline" size="sm" class="rounded-lg h-9">
                            Change
                        </Button>
                    </div>

                    <!-- Total Price Row -->
                    <Sheet>
                        <div class="px-4 py-4 border-t border-stone-100 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-stone-900">Total price</p>
                                <p class="text-sm text-stone-500">{{ formatCurrency(totalPrice) }} <span class="underline">IDR</span></p>
                            </div>
                            <SheetTrigger as-child>
                                <Button variant="outline" size="sm" class="rounded-lg h-9">
                                    Details
                                </Button>
                            </SheetTrigger>
                        </div>
                        
                        <!-- Price Details Drawer -->
                        <SheetContent side="bottom" class="rounded-t-3xl">
                            <SheetHeader class="pb-4 border-b border-stone-200">
                                <SheetTitle class="text-xl">Price details</SheetTitle>
                                <SheetDescription class="sr-only">View price breakdown</SheetDescription>
                            </SheetHeader>
                            
                            <div class="py-6 space-y-4">
                                <div class="flex justify-between text-stone-600">
                                    <span>{{ formatCurrency(property.pricePerNight) }} × {{ nights }} night{{ nights > 1 ? 's' : '' }}</span>
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
                                <div class="flex justify-between pt-4 border-t border-stone-200 font-semibold text-lg">
                                    <span>Total (IDR)</span>
                                    <span>{{ formatCurrency(totalPrice) }}</span>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>

                    <!-- Free Cancellation -->
                    <div class="px-4 py-4 border-t border-stone-100">
                        <p class="font-medium text-stone-900">Free cancellation</p>
                        <p class="text-sm text-stone-500">
                            Cancel within 24 hours for a full refund.
                            <a href="#" class="underline font-medium">Full policy</a>
                        </p>
                    </div>
                </div>
                </div>

                <!-- Step 2: Personal Information -->
                <div v-else-if="currentStep === 2" class="px-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-stone-900 mb-4">Your information</h2>
                        <p class="text-sm text-stone-500 mb-4">We'll use this to send your confirmation</p>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">First name</Label>
                                    <Input 
                                        v-model="guestInfo.firstName"
                                        placeholder="John"
                                        class="mt-1.5"
                                    />
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Last name</Label>
                                    <Input 
                                        v-model="guestInfo.lastName"
                                        placeholder="Doe"
                                        class="mt-1.5"
                                    />
                                </div>
                            </div>
                            
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Email</Label>
                                <Input 
                                    v-model="guestInfo.email"
                                    type="email"
                                    placeholder="john@example.com"
                                    class="mt-1.5"
                                />
                            </div>
                            
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Phone number</Label>
                                <div class="flex gap-2 mt-1.5">
                                    <select 
                                        v-model="guestInfo.countryCode"
                                        class="w-20 px-3 py-2 border border-stone-200 rounded-lg text-sm bg-white"
                                    >
                                        <option value="+62">+62</option>
                                        <option value="+1">+1</option>
                                        <option value="+44">+44</option>
                                        <option value="+61">+61</option>
                                    </select>
                                    <Input 
                                        v-model="guestInfo.phone"
                                        type="tel"
                                        placeholder="812 3456 7890"
                                        class="flex-1"
                                    />
                                </div>
                            </div>
                            
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Special requests (optional)</Label>
                                <Textarea 
                                    v-model="guestInfo.specialRequests"
                                    placeholder="Any special requirements?"
                                    class="mt-1.5"
                                    rows="3"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Credit Card Payment -->
                <div v-else-if="currentStep === 3" class="px-6 space-y-6">
                    <!-- Contact Summary -->
                    <div class="border border-stone-200 rounded-xl p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-stone-900">{{ guestInfo.firstName }} {{ guestInfo.lastName }}</p>
                                <p class="text-sm text-stone-500">{{ guestInfo.email }}</p>
                                <p class="text-sm text-stone-500">{{ guestInfo.countryCode }} {{ guestInfo.phone }}</p>
                            </div>
                            <button @click="currentStep = 2" class="text-sm font-medium underline">
                                Edit
                            </button>
                        </div>
                    </div>
                    
                    <!-- Credit Card Form -->
                    <div>
                        <h2 class="text-lg font-semibold text-stone-900 mb-4">Card details</h2>
                        <div class="space-y-4">
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Card number</Label>
                                <Input 
                                    v-model="cardInfo.number"
                                    placeholder="1234 5678 9012 3456"
                                    class="mt-1.5"
                                    maxlength="19"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">Expiry date</Label>
                                    <Input 
                                        v-model="cardInfo.expiry"
                                        placeholder="MM / YY"
                                        class="mt-1.5"
                                        maxlength="7"
                                    />
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-stone-700">CVV</Label>
                                    <Input 
                                        v-model="cardInfo.cvv"
                                        placeholder="123"
                                        class="mt-1.5"
                                        maxlength="4"
                                        type="password"
                                    />
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-stone-700">Cardholder name</Label>
                                <Input 
                                    v-model="cardInfo.name"
                                    placeholder="Name on card"
                                    class="mt-1.5"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="border-t border-stone-200 pt-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" v-model="agreedToTerms" class="w-5 h-5 mt-0.5 rounded accent-stone-900" />
                            <span class="text-sm text-stone-600">
                                I agree to the <a href="#" class="underline">House Rules</a>, 
                                <a href="#" class="underline">Cancellation Policy</a>, and 
                                <a href="#" class="underline">Terms of Service</a>.
                            </span>
                        </label>
                    </div>
                    
                    <!-- Trust Badge -->
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                        <Shield class="w-6 h-6 text-green-600 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-green-800">Secure Payment</p>
                            <p class="text-sm text-green-700">Your card info is encrypted with SSL.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Booking Confirmed (inline, not separate success page) -->
                <div v-else-if="currentStep === 4" class="px-6 py-8 text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Check class="w-10 h-10 text-green-600" />
                    </div>
                    <h2 class="text-xl font-bold text-stone-900 mb-2">You're all set!</h2>
                    <p class="text-stone-500 mb-6">Your booking has been confirmed.</p>
                    
                    <div class="bg-stone-50 rounded-xl p-4 mb-6 text-left">
                        <div class="flex items-center gap-4 mb-4">
                            <img :src="property.image" :alt="property.name" class="w-16 h-16 rounded-lg object-cover" />
                            <div>
                                <h3 class="font-semibold text-stone-900 text-sm">{{ property.name }}</h3>
                                <p class="text-xs text-stone-500">{{ property.location }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2 text-sm">
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
                            <div class="flex justify-between pt-2 border-t border-stone-200">
                                <span class="font-semibold">Total Paid</span>
                                <span class="font-bold">{{ formatCurrency(totalPrice) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-sm text-stone-500 mb-6">
                        Confirmation sent to <strong>{{ guestInfo.email }}</strong>
                    </p>
                </div>
            </main>

            <!-- CTA Buttons -->
            <!-- Mobile: Fixed bottom, Desktop: Inline -->
            <div v-if="currentStep < 4" class="fixed bottom-0 left-0 right-0 bg-white border-t border-stone-200 p-4 max-w-lg mx-auto md:static md:border-t-0 md:px-6 md:pt-0 md:pb-8">
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
            
            <!-- Step 4: Bottom buttons -->
            <div v-else class="fixed bottom-0 left-0 right-0 bg-white border-t border-stone-200 p-4 max-w-lg mx-auto md:static md:border-t-0 md:px-6 md:pt-0 md:pb-8">
                <div class="flex gap-3">
                    <Button variant="outline" class="flex-1" as="a" href="/">
                        Home
                    </Button>
                    <Button class="flex-1 bg-stone-900 hover:bg-stone-800">
                        View Booking
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
