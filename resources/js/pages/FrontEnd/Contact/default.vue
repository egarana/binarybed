<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FrontEndLayout from '@/layouts/FrontEnd/default.vue';
import ReserveNow from '@/components/FrontEnd/ReserveNow/default.vue';
import { usePage } from '@inertiajs/vue3';
import { MailIcon } from 'lucide-vue-next';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :title="'Contact'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >
        <section class="lg:relative">
            <div class="bg-center bg-cover bg-no-repeat aspect-[5/3] md:aspect-[5/2] lg:absolute lg:inset-0 lg:aspect-auto lg:z-0" :style="{ backgroundImage: `url(/images/${page.props.slug}/contact-001.webp)` }"></div>
            <div class="lg:relative lg:z-10 lg:bg-gradient-to-r lg:from-foreground/80 lg:via-foreground/50">
                <div class="bg-zinc-50 mx-auto max-w-screen-xl px-6 py-10 md:py-16 md:px-10 lg:border-none lg:bg-transparent lg:py-0 lg:px-16 lg:min-h-[400px] lg:flex lg:items-center">
                    <div class="max-w-xl lg:text-white">
                        <h2 class="uppercase font-medium text-xs tracking-widest lg:text-sm">
                            Contact Us
                        </h2>
                        <h1 class="font-medium text-xl mt-4 md:text-2xl lg:text-3xl">
                            Need help or have questions?
                        </h1>
                        <div class="mt-4 space-y-4 lg:mt-6">
                            <p>
                                Whether you have questions, suggestions, or simply want to get in touch here's how you can reach us:
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8 md:py-12 lg:py-16">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16">
                <div class="divide-y divide-zinc-100 lg:grid lg:grid-cols-2 lg:space-y-0 lg:divide-y-0 lg:-mx-14">
                    <div class="pb-8 lg:pb-0 lg:flex lg:flex-col lg:px-14">
                        <h1 class="text-lg font-medium md:text-xl">
                            Quick chat or call?
                        </h1>
                        <p class="mt-4 md:mt-6">
                            Have a question or just want to connect? We're always happy to hear from you.
                        </p>
                        <div class="mt-10">
                            <Button as-child size="lg">
                                <a href="https://wa.me/12345678900" target="_blank" rel="noopener noreferrer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-background !w-5 !h-5" viewBox="0 0 256 256"><path d="M186.68,146.63l-32-16a6,6,0,0,0-6,.38L133,141.46A42.49,42.49,0,0,1,114.54,123L125,107.33a6,6,0,0,0,.38-6l-16-32A6,6,0,0,0,104,66a38,38,0,0,0-38,38,86.1,86.1,0,0,0,86,86,38,38,0,0,0,38-38A6,6,0,0,0,186.68,146.63ZM152,178a74.09,74.09,0,0,1-74-74,26,26,0,0,1,22.42-25.75l12.66,25.32-10.39,15.58a6,6,0,0,0-.54,5.63,54.43,54.43,0,0,0,29.07,29.07,6,6,0,0,0,5.63-.54l15.58-10.39,25.32,12.66A26,26,0,0,1,152,178ZM128,26A102,102,0,0,0,38.35,176.69L26.73,211.56a14,14,0,0,0,17.71,17.71l34.87-11.62A102,102,0,1,0,128,26Zm0,192a90,90,0,0,1-45.06-12.08,6.09,6.09,0,0,0-3-.81,6.2,6.2,0,0,0-1.9.31L40.65,217.88a2,2,0,0,1-2.53-2.53L50.58,178a6,6,0,0,0-.5-4.91A90,90,0,1,1,128,218Z"></path></svg> +1 (234) 567-8900
                                </a>
                            </Button>
                        </div>
                    </div>

                    <div class="pt-8 lg:pt-0 lg:flex lg:flex-col lg:px-14">
                        <h1 class="text-lg font-medium md:text-xl">
                            Let's connect via email
                        </h1>
                        <p class="mt-4 md:mt-6">
                            Our inbox is always open. Reach out for questions, collaborations, or just to say hello.
                        </p>
                        <div class="mt-10 lg:mt-auto">
                            <Button as-child size="lg">
                                <a href="mailto:info@rimbaluna.com">
                                    <MailIcon/> info@rimbaluna.com
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <ReserveNow :slug="page.props.slug" />
    </FrontEndLayout>
</template>