<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/amayavillas.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue'

// Access Inertia props
const page = usePage()
const slug = computed(() => page.props.slug as string)

// Import all attraction JSON files eagerly
const attractionModules = import.meta.glob('/resources/js/json/*/nearby-attractions.json', {
    eager: true,
    import: 'default',
})

// Resolve attractions for slug (fallback to default)
function resolveAttractions(slug: string) {
    return attractionModules[`/resources/js/json/${slug}/nearby-attractions.json`] 
        ?? attractionModules['/resources/js/json/default/nearby-attractions.json'] 
        ?? []
}

const attractions = computed(() => resolveAttractions(slug.value))
</script>

<template>
    <FrontEndLayout
        :title="'Nearby Attractions'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >
        <section class="pt-10 md:pt-16 lg:pt-24">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16">
                <div class="max-w-xl">
                    <h2 class="uppercase font-semibold text-xs tracking-widest lg:text-sm">
                        Nearby Attractions
                    </h2>
                    <h1 class="font-medium text-xl mt-4 md:text-2xl lg:text-3xl">
                        Here are our recommendations for exploring Bali
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            We've hand-picked some of the most incredible places to visit, from stunning beaches to serene temples.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-8 pt-8 pb-8 md:space-y-12 md:pt-12 md:pb-12 lg:space-y-16 lg:pb-16 lg:pt-24">
            <div v-for="attraction in attractions" class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
                <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-black/10 rounded-xs md:aspect-[16/9] lg:shrink-0 lg:w-1/2 lg:aspect-[16/9]" :style="{ backgroundImage: `url(/images/${page.props.slug}/${attraction.image})` }"></div>
                <div class="pt-6 md:pt-8 lg:py-0">
                    <h1 class="text-lg font-medium md:text-xl lg:text-2xl">
                        {{ attraction.name }}
                    </h1>
                    <div class="font-light text-muted-foreground text-sm italic mt-1">
                        Photo courtesy of {{ attraction.credit }}
                    </div>
                    <div class="mt-6 space-y-4">
                        <p v-for="(paragraph, index) in attraction.description.split('\n\n')" :key="index">
                            {{ paragraph }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </FrontEndLayout>
</template>