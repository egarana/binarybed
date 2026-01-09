<script setup lang="ts">
import Layout from './Layout.vue';
import { Button } from '@/components/ui/button';
import { Phone, Mail, MapPin, Clock, MessageCircle, ArrowUpRight } from 'lucide-vue-next';

const contactInfo = {
    phone: '+62 881-0379-90320',
    email: 'info@lakebaturcabin.com',
    address: 'Jl. Raya Penelokan, Kintamani, Bangli, Bali 80652',
    hours: '8:00 AM - 9:00 PM Daily',
    whatsapp: '6288103799032',
};

const whatsappLink = `https://wa.me/${contactInfo.whatsapp}?text=Hello, I would like to inquire about Lake Batur Cabin.`;
</script>

<template>
    <Layout title="Contact">
        <div class="min-h-screen bg-white">

            <!-- Hero -->
            <section class="py-32 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Get in Touch</p>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-light text-stone-900 mb-8 leading-tight">
                            We'd love to<br />
                            <span class="font-serif italic">hear from you</span>
                        </h1>
                        <p class="text-lg text-stone-600 font-light">
                            Questions, inquiries, or ready to book your stay? Our team is here to assist.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Contact Grid -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="grid lg:grid-cols-2 gap-20">
                        
                        <!-- Left: Methods -->
                        <div class="space-y-12">
                            <!-- WhatsApp -->
                            <a :href="whatsappLink" target="_blank" class="block group">
                                <div class="flex items-start gap-6">
                                    <div class="w-14 h-14 rounded-full border border-stone-200 flex items-center justify-center group-hover:bg-stone-900 group-hover:border-stone-900 transition-colors">
                                        <MessageCircle class="w-5 h-5 text-stone-600 group-hover:text-white transition-colors" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-medium text-stone-900">WhatsApp</h3>
                                            <ArrowUpRight class="w-4 h-4 text-stone-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                                        </div>
                                        <p class="text-stone-500 font-light">Fastest response • Usually within 1 hour</p>
                                    </div>
                                </div>
                            </a>
                            
                            <!-- Phone -->
                            <a :href="`tel:${contactInfo.phone}`" class="block group">
                                <div class="flex items-start gap-6">
                                    <div class="w-14 h-14 rounded-full border border-stone-200 flex items-center justify-center group-hover:bg-stone-900 group-hover:border-stone-900 transition-colors">
                                        <Phone class="w-5 h-5 text-stone-600 group-hover:text-white transition-colors" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-stone-900">Phone</h3>
                                        <p class="text-stone-500 font-light">{{ contactInfo.phone }}</p>
                                    </div>
                                </div>
                            </a>
                            
                            <!-- Email -->
                            <a :href="`mailto:${contactInfo.email}`" class="block group">
                                <div class="flex items-start gap-6">
                                    <div class="w-14 h-14 rounded-full border border-stone-200 flex items-center justify-center group-hover:bg-stone-900 group-hover:border-stone-900 transition-colors">
                                        <Mail class="w-5 h-5 text-stone-600 group-hover:text-white transition-colors" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-stone-900">Email</h3>
                                        <p class="text-stone-500 font-light">{{ contactInfo.email }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <!-- Right: Details -->
                        <div class="space-y-12">
                            <!-- Hours -->
                            <div class="flex items-start gap-6">
                                <div class="w-14 h-14 rounded-full border border-stone-200 flex items-center justify-center">
                                    <Clock class="w-5 h-5 text-stone-600" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-stone-900">Hours</h3>
                                    <p class="text-stone-500 font-light">{{ contactInfo.hours }}</p>
                                </div>
                            </div>
                            
                            <!-- Location -->
                            <div class="flex items-start gap-6">
                                <div class="w-14 h-14 rounded-full border border-stone-200 flex items-center justify-center">
                                    <MapPin class="w-5 h-5 text-stone-600" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-stone-900">Location</h3>
                                    <p class="text-stone-500 font-light">{{ contactInfo.address }}</p>
                                    <a href="https://maps.google.com" target="_blank" class="inline-flex items-center gap-1 text-stone-900 text-sm mt-2 hover:underline">
                                        Open in Maps <ArrowUpRight class="w-3 h-3" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-32 bg-stone-50">
                <div class="mx-auto max-w-screen-xl px-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Ready to Book?</p>
                    <h2 class="text-3xl font-light text-stone-900 mb-8">
                        Explore our collection of cabins
                    </h2>
                    <a :href="whatsappLink" target="_blank">
                        <Button size="lg" class="bg-stone-900 hover:bg-stone-800 text-white px-10 py-6 text-sm uppercase tracking-wider">
                            Start Conversation
                        </Button>
                    </a>
                </div>
            </section>

        </div>
    </Layout>
</template>
