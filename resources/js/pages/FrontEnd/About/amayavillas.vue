<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/amayavillas.vue';
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
                            A place to pause and reconnect
                        </h1>
                        <div class="mt-4 space-y-4 lg:mt-6">
                            <p>
                                Welcome to Amaya Villas, a private retreat designed for comfort and calm. Surrounded by Bali’s natural beauty, our villas provide a setting where you can slow down, recharge, and enjoy the island at your own pace.
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
                        Effortless comfort, every day
                    </h1>
                    <div class="mt-4 md:mt-6">
                        <p>
                            From sunrise walks to evenings by the pool, our team ensures your stay is seamless so you can focus on relaxing and enjoying Bali.
                        </p>
                    </div>
                </div>
                <div class="mt-8 md:mt-10 lg:mt-0">
                    <h1 class="text-lg font-medium md:text-xl">
                        Meaningful experiences
                    </h1>
                    <div class="mt-4 md:mt-6">
                        <p>
                            We arrange personalized activities guided nature walks, cooking sessions, and wellness programs—so every guest leaves with lasting memories.
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
                        Your retreat in the heart of Bali
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            Nestled between rice fields and ocean views, our villas are crafted with natural materials and designed to welcome light and fresh air.
                        </p>
                        <p>
                            Every detail from comfortable bedding to locally made amenities—is chosen to create a restful and authentic experience.
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
                        Designed for rest and renewal
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            Each villa combines privacy with modern comforts, giving you space to relax, reflect, and feel at home in Bali.
                        </p>
                        <p>
                            With thoughtful touches and a peaceful atmosphere, we create an environment where every stay feels effortless.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </FrontEndLayout>
</template>