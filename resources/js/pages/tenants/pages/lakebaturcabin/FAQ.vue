<script setup lang="ts">
import Layout from './Layout.vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronDown, Search } from 'lucide-vue-next';

const faqCategories = [
    {
        name: 'Booking',
        faqs: [
            { q: 'How do I make a reservation?', a: 'Reservations can be made through our website or via WhatsApp. Select your preferred cabin and dates, complete the booking form, and proceed with payment. Confirmation will be sent via email.' },
            { q: 'What is the minimum stay?', a: 'Standard minimum is one night. During peak seasons (December-January, July-August), we may require 2-3 nights minimum.' },
            { q: 'Can I modify or cancel my booking?', a: 'Yes, modifications and cancellations are accepted up to 48 hours before check-in. Please refer to our cancellation policy for detailed terms.' },
        ]
    },
    {
        name: 'Your Stay',
        faqs: [
            { q: 'What are check-in and check-out times?', a: 'Check-in from 2:00 PM, check-out by 12:00 PM. Early check-in or late check-out may be arranged upon request, subject to availability.' },
            { q: 'Is breakfast included?', a: 'Most cabin packages include our signature farm-to-table breakfast featuring locally sourced ingredients. Please verify inclusions for your specific booking.' },
            { q: 'Is WiFi available?', a: 'Complimentary high-speed WiFi is available throughout the property, suitable for remote work and streaming.' },
        ]
    },
    {
        name: 'Activities',
        faqs: [
            { q: 'What experiences are available?', a: 'We offer sunrise volcano treks, hot spring visits, coffee plantation tours, traditional village walks, and water activities on Lake Batur. Our concierge can arrange all experiences.' },
            { q: 'Can you arrange airport transfers?', a: 'Yes, private airport transfers are available. The journey from Ngurah Rai Airport takes approximately 2-2.5 hours.' },
        ]
    },
];

const searchQuery = ref('');
const openItems = ref<string[]>([]);

const toggle = (id: string) => {
    const i = openItems.value.indexOf(id);
    i === -1 ? openItems.value.push(id) : openItems.value.splice(i, 1);
};

const isOpen = (id: string) => openItems.value.includes(id);

const filteredCategories = () => {
    if (!searchQuery.value.trim()) return faqCategories;
    const q = searchQuery.value.toLowerCase();
    return faqCategories.map(cat => ({
        ...cat,
        faqs: cat.faqs.filter(f => f.q.toLowerCase().includes(q) || f.a.toLowerCase().includes(q))
    })).filter(cat => cat.faqs.length > 0);
};

const whatsappLink = 'https://wa.me/6288103799032?text=Hello, I have a question.';
</script>

<template>
    <Layout title="FAQ">
        <div class="min-h-screen bg-white">

            <!-- Hero -->
            <section class="py-32 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl mx-auto text-center">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Help Center</p>
                        <h1 class="text-4xl md:text-5xl font-light text-stone-900 mb-8">
                            Frequently Asked <span class="font-serif italic">Questions</span>
                        </h1>
                        
                        <!-- Search -->
                        <div class="relative max-w-md mx-auto">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search..."
                                class="w-full pl-12 pr-4 py-4 bg-stone-50 border-0 rounded-full text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-stone-300 transition-all"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl mx-auto space-y-16">
                        <div v-for="cat in filteredCategories()" :key="cat.name">
                            <p class="text-sm uppercase tracking-[0.2em] text-stone-400 mb-8">{{ cat.name }}</p>
                            
                            <div class="space-y-0 divide-y divide-stone-100">
                                <div v-for="(faq, i) in cat.faqs" :key="i">
                                    <button 
                                        @click="toggle(`${cat.name}-${i}`)"
                                        class="w-full py-6 text-left flex items-start justify-between gap-4"
                                    >
                                        <span class="text-lg text-stone-900 font-light">{{ faq.q }}</span>
                                        <ChevronDown 
                                            class="w-5 h-5 text-stone-400 flex-shrink-0 transition-transform mt-1"
                                            :class="{ 'rotate-180': isOpen(`${cat.name}-${i}`) }"
                                        />
                                    </button>
                                    <div 
                                        v-show="isOpen(`${cat.name}-${i}`)"
                                        class="pb-6"
                                    >
                                        <p class="text-stone-500 font-light leading-relaxed">{{ faq.a }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Results -->
                        <div v-if="filteredCategories().length === 0" class="text-center py-12">
                            <p class="text-stone-500">No results found</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-32 bg-stone-50">
                <div class="mx-auto max-w-screen-xl px-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Still Have Questions?</p>
                    <h2 class="text-2xl font-light text-stone-900 mb-8">
                        We're here to help
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
