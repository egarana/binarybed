<script setup>
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    slug: {
        type: String,
        default: 'default',
    },
});
</script>

<template>
    <section class="pb-8 md:pb-12 lg:pb-16">
        <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16 lg:flex lg:gap-x-20 lg:items-center">
            <div class="bg-zinc-50 border rounded-xs w-full overflow-hidden lg:flex">
                <div class="bg-cover bg-center bg-no-repeat aspect-[4/3] bg-blend-overlay bg-black/10 md:aspect-[16/9] lg:shrink-0 lg:w-1/2 lg:aspect-[16/9]" :style="{ backgroundImage: `url(/images/${slug}/reservenow-001.webp)` }">
                </div>
                <div class="p-6 md:p-8 lg:py-10 lg:px-12 lg:flex lg:flex-col lg:max-w-lg">
                    <h1 class="text-lg font-medium md:text-xl lg:text-xl">
                        Book when it feels right, we're here when you are
                    </h1>
                    <p class="mt-4 md:mt-6">
                        Reserve directly through our site and enjoy the best price available and a seamless booking experience.
                    </p>
                    <div class="mt-10 lg:mt-auto">
                        <Button as-child size="lg">
                            <Link :href="'/contact'">
                                Reserve now
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>