<script setup lang="ts">
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronDown } from 'lucide-vue-next';

const lastUpdated = 'January 1, 2026';
const whatsappLink = 'https://wa.me/6288103799032?text=Hello, I have a question about terms.';

const sections = [
    {
        id: 1,
        title: 'Acceptance of Terms',
        content: 'By using Lake Batur Cabin services, you agree to these Terms and Conditions. If you do not agree, please refrain from using our services. These terms apply to all visitors, users, and guests.'
    },
    {
        id: 2,
        title: 'Reservations',
        content: 'Reservations are subject to availability and confirmation upon payment. We reserve the right to refuse reservations in cases of suspected fraud or policy violations. Guests must provide accurate information; false details may result in cancellation.'
    },
    {
        id: 3,
        title: 'Check-in & Check-out',
        content: 'Check-in from 2:00 PM; check-out by 12:00 PM. Early or late arrangements subject to availability and may incur fees. Valid identification required. The booking holder must be present and at least 18 years of age.'
    },
    {
        id: 4,
        title: 'Payment',
        content: 'Full payment required at booking unless specified otherwise. All prices in IDR including applicable taxes. Prices may change for future bookings; confirmed reservations are unaffected.'
    },
    {
        id: 5,
        title: 'Guest Conduct',
        content: 'Guests must behave responsibly. Prohibited: indoor smoking, excessive noise (10 PM - 7 AM), unauthorized pets, additional guests without arrangement, illegal activities. Violations may result in immediate eviction without refund.'
    },
    {
        id: 6,
        title: 'Liability',
        content: 'Guests are responsible for property damage during their stay. We are not liable for loss or damage to personal belongings. We maintain insurance but cannot be held responsible for circumstances beyond our control.'
    },
    {
        id: 7,
        title: 'Cancellations',
        content: 'Please refer to our Cancellation Policy for detailed terms regarding cancellations and refunds. Refunds processed within 5-7 business days via original payment method.'
    },
    {
        id: 8,
        title: 'Amendments',
        content: 'We may modify these terms at any time. Changes effective immediately upon posting. Continued use constitutes acceptance. We encourage periodic review of these terms.'
    },
];

const openItems = ref<number[]>([1, 2]);

const toggle = (id: number) => {
    const i = openItems.value.indexOf(id);
    i === -1 ? openItems.value.push(id) : openItems.value.splice(i, 1);
};

const isOpen = (id: number) => openItems.value.includes(id);
</script>

<template>
    <Layout title="Terms and Conditions">
        <div class="min-h-screen bg-white">

            <!-- Hero -->
            <section class="py-32 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Legal</p>
                        <h1 class="text-4xl md:text-5xl font-light text-stone-900 mb-6">
                            Terms & <span class="font-serif italic">Conditions</span>
                        </h1>
                        <p class="text-lg text-stone-500 font-light">
                            Last updated: {{ lastUpdated }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Notice -->
            <section class="py-8 bg-stone-50 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl mx-auto text-center">
                        <p class="text-stone-600 font-light">
                            By completing a booking, you agree to these terms, our 
                            <Link href="/privacy-policy" class="underline hover:text-stone-900">Privacy Policy</Link>, and 
                            <Link href="/cancellation-policy" class="underline hover:text-stone-900">Cancellation Policy</Link>.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl mx-auto space-y-0 divide-y divide-stone-100">
                        <div v-for="section in sections" :key="section.id">
                            <button 
                                @click="toggle(section.id)"
                                class="w-full py-8 text-left flex items-start justify-between gap-4"
                            >
                                <div class="flex items-start gap-6">
                                    <span class="text-sm text-stone-400 mt-1">{{ String(section.id).padStart(2, '0') }}</span>
                                    <span class="text-lg text-stone-900 font-light">{{ section.title }}</span>
                                </div>
                                <ChevronDown 
                                    class="w-5 h-5 text-stone-400 flex-shrink-0 transition-transform mt-1"
                                    :class="{ 'rotate-180': isOpen(section.id) }"
                                />
                            </button>
                            <div 
                                v-show="isOpen(section.id)"
                                class="pb-8 pl-14"
                            >
                                <p class="text-stone-500 font-light leading-relaxed">{{ section.content }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-32 bg-stone-50">
                <div class="mx-auto max-w-screen-xl px-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Questions?</p>
                    <h2 class="text-2xl font-light text-stone-900 mb-8">
                        We're here to clarify
                    </h2>
                    <a :href="whatsappLink" target="_blank">
                        <Button size="lg" class="bg-stone-900 hover:bg-stone-800 text-white px-10 py-6 text-sm uppercase tracking-wider">
                            Contact Us
                        </Button>
                    </a>
                </div>
            </section>

        </div>
    </Layout>
</template>
