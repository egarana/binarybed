<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/amayavillas.vue';
import { Link, usePage } from '@inertiajs/vue3';
import ReserveNow from '@/components/FrontEnd/ReserveNow/default.vue';
import { Button } from '@/components/ui/button';
import { Bed, Binoculars, Scaling, UsersIcon } from 'lucide-vue-next';
import Villas from '@/json/amayavillas/villas.json';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :host="page.props.host"
        :title="'Home'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >

        <section class="min-h-dvh bg-cover bg-center bg-blend-overlay bg-no-repeat relative flex flex-col justify-start pt-26 pb-8 -mt-[69px] md:pt-0 md:pb-12 lg:justify-center lg:-mt-[77px] lg:pt-14" :style="{ backgroundImage: `url(/images/${page.props.slug}/home-001.webp)` }">
            <div class="flex-grow absolute inset-0 z-0 bg-gradient-to-b from-foreground/75 from-5% via-foreground/20 via-20% to-foreground/65 to-90% md:grow-0 lg:from-foreground/90 lg:via-foreground/10 lg:via-40% lg:to-foreground/90"></div>
            <div class="mt-auto max-w-xl text-left ps-6 pe-16 z-10 w-full md:px-10 lg:mt-auto lg:mb-4 lg:px-24 lg:mx-auto lg:max-w-screen-xl">
                <h2 class="text-white uppercase font-thin text-xs tracking-widest md:text-sm">
                    Designed for comfort and ease
                </h2>
                <h1 class="text-white text-2xl mt-4 leading-8 font-medium max-w-2xl md:text-4xl md:leading-snug lg:text-5xl lg:leading-tight">
                    Private villas with modern design in Bali
                </h1>
                <div class="mt-6 lg:mt-10">
                    <Button as-child size="lg" variant="default">
                        <Link :href="`/villas`">
                            See all villas
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <section class="bg-zinc-50 py-10 md:py-16 lg:py-20">
            <div class="relative mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16">
                <h2 class="uppercase font-medium text-xs tracking-widest lg:pr-6 lg:top-1/2 lg:-translate-y-1/2 lg:text-sm lg:absolute lg:bg-zinc-50">
                    A stay designed for relaxation and comfort
                </h2>
                <div class="lg:w-3/5 lg:pr-12">
                    <div class="hidden lg:border-b lg:border-b-muted-foreground/25 lg:block"></div>
                </div>
            </div>
            <div class="mx-auto max-w-screen-xl px-6 mt-4 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-start lg:mt-10">
                <div class="lg:shrink-0 lg:max-w-xs">
                    <h1 class="font-thin text-2xl">
                        Welcome to Amaya Villas, where every detail is created to give you peace
                    </h1>
                </div>
                <div class="mt-4 space-y-4 lg:mt-0 lg:grid lg:grid-cols-2 lg:gap-11">
                    <p>
                        Enjoy spacious villas designed with modern amenities and thoughtful details, where comfort meets style and every moment of your stay is made to feel effortless and truly memorable.
                    </p>
                    <p>
                        Whether you're resting after a day of exploration, enjoying a private meal, every corner of our villas is designed to feel personal and truly your own.
                    </p>
                </div>
            </div>
        </section>

        <section class="pt-8 md:pt-10 lg:pt-12">
            <div class="mx-auto max-w-screen-xl lg:flex lg:flex-wrap lg:items-end lg:justify-between">
                <div class="px-6 md:px-10 lg:px-16 lg:order-1">
                    <h1 class="text-lg font-medium md:text-xl">
                        Villa collection
                    </h1>
                    <p class="mt-4">
                        Enjoy our comfortable villas, which are the ideal setting for relaxation in Bali.
                    </p>
                </div>
                <div class="flex snap-x snap-mandatory scroll-pl-6 overflow-x-scroll scrollbar-hide mt-8 gap-4 px-6 md:gap-6 md:px-10 md:scroll-pl-10 lg:px-16 lg:grid lg:grid-cols-3 lg:order-3 lg:basis-full lg:shrink-0">
                    <a
                        target="_blank"
                        v-for="unit in Villas.slice(0, 3)"
                        :href="`${unit.url}`"
                        class="snap-always snap-start flex flex-col shrink-0 basis-11/12 relative overflow-hidden rounded-xs group md:basis-8/12"
                    >
                        <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-black/10 md:aspect-[16/8] lg:aspect-[16/11]" :style="{ backgroundImage: `url(/images/${page.props.slug}/${unit.image})` }"></div>
                        <div class="bg-background flex-grow px-5 flex flex-col border border-t-0 rounded-b-xs lg:px-6">
                            <div class="pt-4 pb-6 lg:pt-5 lg:pb-7">
                                <h1 class="font-medium">
                                    {{ unit.name }}
                                </h1>
                                <div class="mt-4 flex items-center gap-x-3 gap-y-2.5 flex-wrap lg:mt-4">
                                    <div class="unit-higlight-container">
                                        <Scaling class="unit-higlight-icon"/>
                                        <div>
                                            {{ unit.size }} sqm
                                        </div>
                                    </div>
                                    <div class="unit-higlight-container">
                                        <Bed class="unit-higlight-icon"/>
                                        <div>
                                            {{ unit.bed_size }} bed
                                        </div>
                                    </div>
                                    <div class="unit-higlight-container">
                                        <Binoculars class="unit-higlight-icon"/>
                                        <div>
                                            {{ unit.view }} view
                                        </div>
                                    </div>
                                    <div class="unit-higlight-container">
                                        <UsersIcon class="unit-higlight-icon"/>
                                        <div>
                                            {{ unit.occupancy }} {{ unit.occupancy === 1 ? 'guest' : 'guests' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="px-6 mt-10 md:px-10 lg:mt-0 lg:px-16 lg:order-2">
                    <Button as-child size="lg" variant="outline">
                        <Link :href="`/villas`">
                            See all villas
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <section class="py-8 md:py-12 lg:py-16">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
                <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-foreground/10 rounded-xs md:aspect-[16/9] lg:shrink-0 lg:w-3/5 lg:aspect-[16/9] lg:order-2" :style="{ backgroundImage: `url(/images/${page.props.slug}/uluwatu-temple.webp)` }"></div>
                <div class="pt-6 md:pt-8 lg:py-0 lg:order-1">
                    <h1 class="text-lg font-medium md:text-xl lg:text-2xl">
                        Uncover local treasures around Bali
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            Step outside and experience the soul of Bali majestic landscapes, timeless culture, and hidden gems at your doorstep.
                        </p>
                    </div>
                    <div class="mt-10">
                        <Button as-child size="lg">
                            <Link :href="`/nearby-attractions`">
                                See attractions
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <ReserveNow :slug="page.props.slug" />
        
    </FrontEndLayout>
</template>