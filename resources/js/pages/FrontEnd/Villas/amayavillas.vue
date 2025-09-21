<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FrontEndLayout from '@/layouts/FrontEnd/amayavillas.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Bed, Binoculars, Scaling, UsersIcon } from 'lucide-vue-next';
import Villas from '@/json/amayavillas/villas.json';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :title="'Villas'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >
        <section class="pt-10 md:pt-16 lg:pt-24">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16">
                <div class="max-w-xl">
                    <h2 class="uppercase font-semibold text-xs tracking-widest lg:text-sm">
                        Villas
                    </h2>
                    <h1 class="font-medium text-xl mt-4 md:text-2xl lg:text-3xl">
                        Where every moment feels timeless
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            Our villas are thoughtfully designed to blend with their surroundings, providing comfort, privacy, and an authentic connection to Bali’s landscape and culture.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="space-y-8 pt-8 pb-8 md:space-y-12 md:pt-12 md:pb-12 lg:space-y-16 lg:pb-16 lg:pt-24">
            <div v-for="unit in Villas" class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
                <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-black/10 rounded-xs md:aspect-[16/9] lg:shrink-0 lg:w-3/5 lg:aspect-[16/9]" :style="{ backgroundImage: `url(/images/${page.props.slug}/${unit.image})` }"></div>
                <div class="pt-6 md:pt-8 lg:py-0">
                    <h1 class="text-lg font-medium md:text-xl lg:text-2xl">
                        {{ unit.name }}
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p v-for="(paragraph, index) in unit.description.split('\n\n')" :key="index">
                            {{ paragraph }}
                        </p>
                    </div>
                    <div class="mt-4 flex items-center gap-x-3 gap-y-2.5 flex-wrap lg:mt-4 lg:gap-x-4">
                        <div class="unit-higlight-container">
                            <Scaling class="unit-higlight-icon"/>
                            <div>
                                {{ unit.size }} sqm
                            </div>
                        </div>
                        <div class="unit-higlight-container">
                            <Bed class="unit-higlight-icon"/>
                            <div>
                                {{ unit.bed_size }}
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
                    <div class="mt-10">
                        <Button as-child size="lg">
                            <a :href="`${unit.url}`" target="_blank">
                                See details
                            </a>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </FrontEndLayout>
</template>