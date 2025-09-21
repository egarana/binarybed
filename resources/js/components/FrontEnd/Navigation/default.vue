<script setup lang="ts">
import { Button } from "@/components/ui/button";
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/ui/sheet"
import { Link } from "@inertiajs/vue3";
import { Menu } from "lucide-vue-next";
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useWindowScroll } from '@vueuse/core';

const props = defineProps({
    host: {
        type: String,
        default: 'default.binarybed.com',
    },
    address: {
        type: Object,
        default: () => ({
            text: '123 Example Street, Downtown District, Example City, Example State/Province, 12345, Country',
            url: 'https://www.google.com/maps/place/123+Example+Street,+Example+City,+12345',
        }),
    },
    cta: {
        type: Object,
        default: () => ({
            enabled: true,
            text: 'Reserve now',
            url: '/contact',
        }),
    },
    whatsapp: {
        type: Object,
        default: () => ({
            enabled: true,
            text: '+1 (234) 567-8900',
            url: 'https://wa.me/12345678900',
        }),
    },
    primaryLinks: {
        type: Array,
        default: () => [
            {
                label: 'Deluxe Garden Cottage',
                url: '/accommodations/deluxe-garden-cottage',
            },
            {
                label: 'Premium Horizon Suite',
                url: '/accommodations/premium-horizon-suite',
            },
            {
                label: 'Family Pool Villa',
                url: '/accommodations/family-pool-villa',
            },
        ],
    },
    navDiscoverLinks: {
        type: Array,
        default: () => [
            {
                label: 'Accommodations',
                url: '/accommodations'
            },
            {
                label: 'Nearby Attractions',
                url: '/nearby-attractions'
            },
        ],
    },
    navCompanyLinks: {
        type: Array,
        default: () => [
            {
                label: 'About Us',
                url: '/about'
            },
            {
                label: 'Contact',
                url: '/contact'
            },
        ],
    },
});

const { y } = useWindowScroll();
const isHome = computed(() => route().current('index'));
const isLg = ref(false)

// Detect if scrolled more than 10px
const isScrolled = computed(() => y.value > 1);

const navbarClasses = computed(() => {
    if (isHome.value) {
        return isScrolled.value ? 'scrolled' : 'not-scrolled'
    }
    return 'default'
})

function updateMedia() {
    isLg.value = window.matchMedia('(min-width: 1024px)').matches
}

function isExternal(url: string) {
	return /^https?:\/\//.test(url)
}

onMounted(() => {
    updateMedia()
    window.addEventListener('resize', updateMedia)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateMedia)
})
</script>

<template>
    <section id="navigation" :class="navbarClasses">
        <div class="navigation-wrap lg:py-2.5">
            <div class="mx-auto max-w-screen-xl px-6 py-4 flex items-center justify-between md:gap-x-6 md:px-10 lg:px-16 lg:gap-x-10 lg:py-2">
                <div>
                    <Link href="/" class="block h-7 w-auto md:h-7 lg:h-10" aria-label="Navigation logo">
                        <slot name="logo" />
                    </Link>
                </div>
                <div class="flex items-center">
                    <ul class="hidden list-none items-center me-2 lg:flex" role="navigation" aria-label="Menu Links">
                        <li v-for="(link, index) in primaryLinks" :key="link.label">
                            <Button as-child variant="ghost" class="nav-primary-link">
                                <component
                                    :is="isExternal(link.url) ? 'a' : Link"
                                    class="nav-link"
                                    :href="link.url"
                                    :target="isExternal(link.url) ? '_blank' : null"
                                    :rel="isExternal(link.url) ? 'noopener noreferrer' : null"
                                >
                                    {{ link.label }}
                                </component>
                            </Button>
                        </li>
                    </ul>
                    <Button v-if="cta.enabled" as-child class="reserve-now-nav-btn hover:cursor-pointer me-2 leading-0 lg:me-2">
                        <component
                            :is="isExternal(cta.url) ? 'a' : Link"
                            :href="cta.url"
                            :target="isExternal(cta.url) ? '_blank' : null"
                            :rel="isExternal(cta.url) ? 'noopener noreferrer' : null"
                        >
                            {{ cta.text }}
                        </component>
                    </Button>
                    <Button v-if="whatsapp.enabled" as-child variant="outline" :size="'icon'" class="whatsapp-nav-btn hover:cursor-pointer me-2 leading-0 lg:me-2">
                        <a target="_blank" :href="whatsapp.url" rel="noopener noreferrer" class="lg:flex lg:items-center lg:gap-1.5">
                            <svg class="!w-5 !h-5 fill-[#0a0a0a]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path d="M185.79,148.42l-32-16a4,4,0,0,0-4,.25l-16.64,11.1a44.56,44.56,0,0,1-20.91-20.91l11.1-16.64a4,4,0,0,0,.25-4l-16-32A4,4,0,0,0,104,68a36,36,0,0,0-36,36,84.09,84.09,0,0,0,84,84,36,36,0,0,0,36-36A4,4,0,0,0,185.79,148.42ZM152,180a76.08,76.08,0,0,1-76-76,28,28,0,0,1,25.58-27.9l13.8,27.61-11,16.54A4,4,0,0,0,104,124a52.43,52.43,0,0,0,28,28,4,4,0,0,0,3.76-.37l16.54-11,27.61,13.8A28,28,0,0,1,152,180ZM128,28A100,100,0,0,0,40.53,176.5l-11.9,35.69a12,12,0,0,0,15.18,15.18l35.69-11.9A100,100,0,1,0,128,28Zm0,192a92,92,0,0,1-46.07-12.35,4.05,4.05,0,0,0-2-.54,3.93,3.93,0,0,0-1.27.21L41.28,219.78a4,4,0,0,1-5.06-5.06l12.46-37.38a4,4,0,0,0-.33-3.27A92,92,0,1,1,128,220Z"></path></svg>
                        </a>
                    </Button>
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button variant="outline" size="icon" class="menu-nav-btn hover:cursor-pointer">
                                <Menu class="stroke-1" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent class="navigation-container w-full px-6 overflow-y-auto md:px-10 lg:px-12">
                            <SheetHeader>
                                <SheetTitle></SheetTitle>
                                <SheetDescription></SheetDescription>
                            </SheetHeader>
                            <div class="mt-4">
                                <div class="mt-6 lg:mt-4">
                                    <Link href="/" class="inline-block" aria-label="Navigation logo">
                                        <slot name="logoFull" />
                                    </Link>
                                </div>
                                <ul class="list-none space-y-6 mt-10 lg:hidden" role="navigation" aria-label="Menu Links">
                                    <li v-for="(link, index) in primaryLinks" :key="link.label">
                                        <component
                                            :is="isExternal(link.url) ? 'a' : Link"
                                            class="nav-link"
                                            :href="link.url"
                                            :target="isExternal(link.url) ? '_blank' : null"
                                            :rel="isExternal(link.url) ? 'noopener noreferrer' : null"
                                        >
                                            {{ link.label }}
                                        </component>
                                    </li>
                                </ul>
                                <ul class="list-none space-y-6 mt-8 pt-8 border-t lg:mt-10 lg:pt-0 lg:border-0" role="navigation" aria-label="Menu Links">
                                    <li v-for="(link, index) in navDiscoverLinks" :key="link.label">
                                        <Link class="nav-link" :href="link.url">
                                            {{ link.label }}
                                        </Link>
                                    </li>
                                </ul>
                                <ul class="list-none space-y-6 mt-8 pt-8 border-t" role="navigation" aria-label="Menu Links">
                                    <li v-for="(link, index) in navCompanyLinks" :key="link.label">
                                        <Link class="nav-link" :href="link.url">
                                            {{ link.label }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                            <SheetFooter class="px-0 py-8">
                                <div>
                                    <a class="block text-xs text-muted-foreground opacity-70 hover:opacity-100" target="_blank" :href="address.url" rel="noopener noreferrer">
                                        {{ address.text }}
                                    </a>
                                </div>
                                <!-- <SheetClose as-child></SheetClose> -->
                            </SheetFooter>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </div>
    </section>
</template>