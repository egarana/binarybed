<script setup lang="ts">
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const lastUpdated = 'January 1, 2026';
const whatsappLink = 'https://wa.me/6288103799032?text=Hello, I have a question about cancellation.';
</script>

<template>
    <Layout title="Cancellation Policy">
        <div class="min-h-screen bg-white">

            <!-- Hero -->
            <section class="py-32 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Policies</p>
                        <h1 class="text-4xl md:text-5xl font-light text-stone-900 mb-6">
                            Cancellation <span class="font-serif italic">Policy</span>
                        </h1>
                        <p class="text-lg text-stone-500 font-light">
                            Last updated: {{ lastUpdated }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl mx-auto">
                        
                        <!-- Intro -->
                        <p class="text-lg text-stone-600 font-light leading-relaxed mb-16">
                            We understand that plans can change. Our cancellation policy is designed to be fair and transparent while respecting the nature of our boutique accommodations.
                        </p>

                        <!-- Timeline -->
                        <div class="mb-16">
                            <p class="text-sm uppercase tracking-[0.2em] text-stone-400 mb-8">Cancellation Timeline</p>
                            
                            <div class="space-y-8">
                                <div class="flex gap-8">
                                    <div class="w-24 flex-shrink-0">
                                        <p class="text-2xl font-light text-stone-900">7+</p>
                                        <p class="text-sm text-stone-400">days</p>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-stone-900 mb-1">Full refund</p>
                                        <p class="text-stone-500 font-light">Minus 3% processing fee. Refund within 5-7 business days.</p>
                                    </div>
                                </div>
                                
                                <div class="border-t border-stone-100"></div>
                                
                                <div class="flex gap-8">
                                    <div class="w-24 flex-shrink-0">
                                        <p class="text-2xl font-light text-stone-900">3-7</p>
                                        <p class="text-sm text-stone-400">days</p>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-stone-900 mb-1">50% refund</p>
                                        <p class="text-stone-500 font-light">Half of total booking amount returned.</p>
                                    </div>
                                </div>
                                
                                <div class="border-t border-stone-100"></div>
                                
                                <div class="flex gap-8">
                                    <div class="w-24 flex-shrink-0">
                                        <p class="text-2xl font-light text-stone-900">&lt;3</p>
                                        <p class="text-sm text-stone-400">days</p>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-stone-900 mb-1">No refund</p>
                                        <p class="text-stone-500 font-light">Full amount retained due to late cancellation.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modifications -->
                        <div class="mb-16">
                            <p class="text-sm uppercase tracking-[0.2em] text-stone-400 mb-8">Modifications</p>
                            <div class="space-y-4 text-stone-600 font-light leading-relaxed">
                                <p>One complimentary date modification is permitted per booking, subject to availability.</p>
                                <p>Additional modifications incur a fee of IDR 100,000. Price differences for new dates apply.</p>
                                <p>All modification requests must be made at least 48 hours before check-in.</p>
                            </div>
                        </div>

                        <!-- Special Circumstances -->
                        <div class="mb-16">
                            <p class="text-sm uppercase tracking-[0.2em] text-stone-400 mb-8">Special Circumstances</p>
                            <p class="text-stone-600 font-light leading-relaxed mb-4">
                                We may offer flexible terms for exceptional circumstances including natural disasters, travel restrictions, documented medical emergencies, or bereavement. Each case is reviewed individually.
                            </p>
                        </div>

                        <!-- How to Cancel -->
                        <div class="bg-stone-50 rounded-lg p-8">
                            <p class="text-sm uppercase tracking-[0.2em] text-stone-400 mb-6">How to Cancel</p>
                            <ol class="space-y-3 text-stone-600 font-light">
                                <li class="flex gap-4">
                                    <span class="text-stone-400">1.</span>
                                    Contact us via WhatsApp or email with your booking reference
                                </li>
                                <li class="flex gap-4">
                                    <span class="text-stone-400">2.</span>
                                    Receive cancellation confirmation
                                </li>
                                <li class="flex gap-4">
                                    <span class="text-stone-400">3.</span>
                                    Refund processed within 5-7 business days (if applicable)
                                </li>
                            </ol>
                        </div>

                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-32 bg-stone-50">
                <div class="mx-auto max-w-screen-xl px-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Questions?</p>
                    <h2 class="text-2xl font-light text-stone-900 mb-8">
                        We're happy to clarify
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
