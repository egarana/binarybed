<script setup lang="ts">
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ArrowRight, Leaf, Heart, Award } from 'lucide-vue-next';

const values = [
    { 
        title: 'Sustainability', 
        desc: 'Committed to eco-conscious practices that honor our environment',
        icon: Leaf 
    },
    { 
        title: 'Authenticity', 
        desc: 'Genuine Balinese experiences rooted in local culture',
        icon: Heart 
    },
    { 
        title: 'Excellence', 
        desc: 'Uncompromising quality in every detail of your stay',
        icon: Award 
    },
];
</script>

<template>
    <Layout title="About Us">
        <div class="min-h-screen bg-white">

            <!-- Hero -->
            <section class="py-32 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-3xl">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Our Story</p>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-light text-stone-900 mb-8 leading-tight">
                            A sanctuary born from<br />
                            <span class="font-serif italic">passion and purpose</span>
                        </h1>
                    </div>
                </div>
            </section>

            <!-- Story -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="grid lg:grid-cols-2 gap-20 items-start">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Since 2020</p>
                            <h2 class="text-2xl md:text-3xl font-light text-stone-900 mb-8">
                                Where volcanic beauty meets refined hospitality
                            </h2>
                        </div>
                        <div class="space-y-6">
                            <p class="text-lg text-stone-600 font-light leading-relaxed">
                                Lake Batur Cabin was born from a simple yet profound dream: to share the breathtaking beauty of Kintamani with discerning travelers seeking more than just accommodation.
                            </p>
                            <p class="text-lg text-stone-600 font-light leading-relaxed">
                                Perched on the shores of the sacred Lake Batur, with unobstructed views of the majestic Mount Batur volcano, our collection of thoughtfully designed cabins offers an unparalleled retreat into nature.
                            </p>
                            <p class="text-lg text-stone-600 font-light leading-relaxed">
                                What began as a single cabin has evolved into a curated collection of six unique retreats, each crafted to harmonize with the natural landscape while providing the comforts of modern luxury.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Divider with Stats -->
            <section class="py-20 bg-stone-50">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-12 text-center">
                        <div>
                            <p class="text-3xl font-light text-stone-900 mb-1">2,500+</p>
                            <p class="text-sm text-stone-500">Guests</p>
                        </div>
                        <div>
                            <p class="text-3xl font-light text-stone-900 mb-1">4.9</p>
                            <p class="text-sm text-stone-500">Rating</p>
                        </div>
                        <div>
                            <p class="text-3xl font-light text-stone-900 mb-1">6</p>
                            <p class="text-sm text-stone-500">Cabins</p>
                        </div>
                        <div>
                            <p class="text-3xl font-light text-stone-900 mb-1">4+</p>
                            <p class="text-sm text-stone-500">Years</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Values -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="text-center mb-20">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Our Philosophy</p>
                        <h2 class="text-3xl md:text-4xl font-light text-stone-900">
                            Values that guide us
                        </h2>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-16">
                        <div v-for="value in values" :key="value.title" class="text-center">
                            <div class="w-16 h-16 rounded-full border border-stone-200 flex items-center justify-center mx-auto mb-6">
                                <component :is="value.icon" class="w-6 h-6 text-stone-700" />
                            </div>
                            <h3 class="text-xl font-medium text-stone-900 mb-3">{{ value.title }}</h3>
                            <p class="text-stone-500 font-light">{{ value.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-32 bg-stone-900">
                <div class="mx-auto max-w-screen-xl px-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Experience Lake Batur</p>
                    <h2 class="text-3xl md:text-4xl font-light text-white mb-8">
                        Begin your journey with us
                    </h2>
                    <Link href="/cabins">
                        <Button size="lg" class="bg-white text-stone-900 hover:bg-stone-100 px-10 py-6 text-sm uppercase tracking-wider">
                            View Our Cabins
                            <ArrowRight class="w-4 h-4 ml-3" />
                        </Button>
                    </Link>
                </div>
            </section>

        </div>
    </Layout>
</template>
