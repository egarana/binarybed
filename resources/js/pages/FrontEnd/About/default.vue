<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/default.vue';
import { usePage } from '@inertiajs/vue3';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :title="'About Us'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >
        <section class="lg:relative">
            <div class="bg-center bg-cover bg-no-repeat aspect-[5/3] md:aspect-[5/2] lg:absolute lg:inset-0 lg:aspect-auto lg:z-0" :style="{ backgroundImage: `url(/images/${page.props.slug}/about-001.webp)` }"></div>
            <div class="lg:relative lg:z-10 lg:bg-gradient-to-r lg:from-foreground/80 lg:via-foreground/50">
                <div class="bg-zinc-50 mx-auto max-w-screen-xl px-6 py-10 md:py-16 md:px-10 lg:border-none lg:bg-transparent lg:py-0 lg:px-16 lg:min-h-[400px] lg:flex lg:items-center">
                    <div class="max-w-xl lg:text-white">
                        <h2 class="uppercase font-medium text-xs tracking-widest lg:text-sm">
                            About Us
                        </h2>
                        <h1 class="font-medium text-xl mt-4 md:text-2xl lg:text-3xl">
                            Where jungle spirit meets moonlit
                        </h1>
                        <div class="mt-4 space-y-4 lg:mt-6">
                            <p>
                                Welcome to Rimba Luna an intimate hideaway in Karangasem where untouched rainforest, gentle ocean breezes, and the magic of Bali's moon converge to awaken every sense.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pt-8 md:pt-12 lg:bg-zinc-50 lg:py-20">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
                <div>
                    <h1 class="text-lg font-medium md:text-xl">
                        Effortless serenity, every moment
                    </h1>
                    <div class="mt-4 md:mt-6">
                        <p>
                            From sunrise meditation to sunset dining under the palms, our team choreographs seamless experiences so you can simply breathe, unwind, and feel Bali's rhythm.
                        </p>
                    </div>
                </div>
                <div class="mt-8 md:mt-10 lg:mt-0">
                    <h1 class="text-lg font-medium md:text-xl">
                        Memories crafted naturally
                    </h1>
                    <div class="mt-4 md:mt-6">
                        <p>
                            We curate bespoke journeys—private trekking to sacred waterfalls, hands-on cooking classes, and moon-lit yoga—designed to leave you inspired long after you depart.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8 md:py-12 lg:py-16">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
                <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-foreground/10 rounded-xs md:aspect-[16/9] lg:shrink-0 lg:w-3/5 lg:aspect-[16/9]" :style="{ backgroundImage: `url(/images/${page.props.slug}/about-002.webp)` }"></div>
                <div class="pt-6 md:pt-8 lg:py-0">
                    <h1 class="text-lg font-medium md:text-xl lg:text-2xl">
                        Your sanctuary in the heart of Karangasem
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            Tucked between emerald rice terraces and the azure Lombok Strait, Rimba Luna offers just a handful of suites and jungle lodges, each handcrafted with natural materials and open to the breeze.
                        </p>
                        <p>
                            Thoughtful touches—organic linens, artisan bath amenities, and panoramic decks—invite quiet reflection and deep rest.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-8 md:pb-12 lg:pb-16">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
                <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-foreground/10 rounded-xs md:aspect-[16/9] lg:order-2 lg:shrink-0 lg:w-3/5 lg:aspect-[16/9]" :style="{ backgroundImage: `url(/images/${page.props.slug}/about-003.webp)` }"></div>
                <div class="pt-6 md:pt-8 lg:order-1 lg:py-0">
                    <h1 class="text-lg font-medium md:text-xl lg:text-2xl">
                        Your sanctuary in the heart of Karangasem
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            Tucked between emerald rice terraces and the azure Lombok Strait, Rimba Luna offers just a handful of suites and jungle lodges, each handcrafted with natural materials and open to the breeze.
                        </p>
                        <p>
                            Thoughtful touches—organic linens, artisan bath amenities, and panoramic decks—invite quiet reflection and deep rest.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </FrontEndLayout>
</template>