<script setup lang="ts">
import { computed } from 'vue';
import Layout from './Layout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useResourceStore } from '@/stores/useResourceStore';
import { ArrowRight, Star } from 'lucide-vue-next';

const page = usePage();
const branding = computed(() => page.props.branding as { name: string; tagline?: string } | undefined);
const resourceStore = useResourceStore();

// Get first unit image for hero
const heroImage = computed(() => {
    const unit = resourceStore.units[0];
    return unit?.media?.[0]?.original_url || null;
});

const features = [
    { title: 'Breathtaking Views', desc: 'Wake up to Mount Batur sunrises' },
    { title: 'Premium Comfort', desc: 'Luxury amenities in every cabin' },
    { title: 'Authentic Experience', desc: 'Immerse in Balinese culture' },
    { title: 'Personalized Service', desc: 'Dedicated concierge support' },
];
</script>

<template>
    <Layout :title="branding?.name || 'Lake Batur Cabin'">
        <div class="min-h-screen bg-white">

            <!-- Hero Section - Elegant Full Width -->
            <section class="relative min-h-[90vh] flex items-center">
                <!-- Background -->
                <div class="absolute inset-0 bg-stone-100">
                    <div v-if="heroImage" class="absolute inset-0">
                        <img :src="heroImage" alt="Lake Batur Cabin" class="w-full h-full object-cover opacity-40" />
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-b from-white/60 via-white/30 to-white"></div>
                </div>
                
                <div class="relative mx-auto max-w-screen-xl px-6 py-32 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-500 mb-6">Kintamani, Bali</p>
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-light text-stone-900 mb-8 leading-tight">
                        Lake Batur<br />
                        <span class="font-serif italic">Cabin</span>
                    </h1>
                    <p class="text-lg md:text-xl text-stone-600 max-w-xl mx-auto mb-12 font-light">
                        A sanctuary of tranquility where volcanic landscapes meet timeless elegance
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/cabins">
                            <Button size="lg" class="bg-stone-900 hover:bg-stone-800 text-white px-10 py-6 text-sm uppercase tracking-wider">
                                Explore Cabins
                            </Button>
                        </Link>
                        <Link href="/about">
                            <Button size="lg" variant="outline" class="border-stone-900 text-stone-900 hover:bg-stone-50 px-10 py-6 text-sm uppercase tracking-wider">
                                Our Story
                            </Button>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="mx-auto max-w-screen-xl px-6">
                <div class="border-t border-stone-200"></div>
            </div>

            <!-- Features - Minimal Grid -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
                        <div v-for="feature in features" :key="feature.title" class="text-center">
                            <h3 class="text-lg font-medium text-stone-900 mb-2">{{ feature.title }}</h3>
                            <p class="text-stone-500 font-light">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quote Section -->
            <section class="py-32 bg-stone-50">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-3xl mx-auto text-center">
                        <div class="flex justify-center gap-1 mb-8">
                            <Star v-for="i in 5" :key="i" class="w-4 h-4 text-amber-500 fill-amber-500" />
                        </div>
                        <blockquote class="text-2xl md:text-3xl font-light text-stone-700 italic font-serif mb-8">
                            "An extraordinary escape. The views, the serenity, the attention to detail — absolutely unforgettable."
                        </blockquote>
                        <p class="text-sm uppercase tracking-wider text-stone-500">— Featured Guest Review</p>
                    </div>
                </div>
            </section>

            <!-- Stats - Refined -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
                        <div class="text-center">
                            <p class="text-4xl md:text-5xl font-light text-stone-900 mb-2">2,500+</p>
                            <p class="text-sm uppercase tracking-wider text-stone-500">Guests Hosted</p>
                        </div>
                        <div class="text-center">
                            <p class="text-4xl md:text-5xl font-light text-stone-900 mb-2">4.9</p>
                            <p class="text-sm uppercase tracking-wider text-stone-500">Average Rating</p>
                        </div>
                        <div class="text-center">
                            <p class="text-4xl md:text-5xl font-light text-stone-900 mb-2">6</p>
                            <p class="text-sm uppercase tracking-wider text-stone-500">Unique Cabins</p>
                        </div>
                        <div class="text-center">
                            <p class="text-4xl md:text-5xl font-light text-stone-900 mb-2">4+</p>
                            <p class="text-sm uppercase tracking-wider text-stone-500">Years of Excellence</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA - Elegant -->
            <section class="py-32 bg-stone-900">
                <div class="mx-auto max-w-screen-xl px-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Begin Your Journey</p>
                    <h2 class="text-3xl md:text-4xl font-light text-white mb-8">
                        Experience the extraordinary
                    </h2>
                    <Link href="/cabins">
                        <Button size="lg" class="bg-white text-stone-900 hover:bg-stone-100 px-10 py-6 text-sm uppercase tracking-wider">
                            View Accommodations
                            <ArrowRight class="w-4 h-4 ml-3" />
                        </Button>
                    </Link>
                </div>
            </section>

        </div>
    </Layout>
</template>
