<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { GitBranchIcon, Home, Menu } from 'lucide-vue-next';
import { useMoreDrawer } from '@/composables/useMoreDrawer';
import { type Component, ref, watch } from 'vue';
import { useWindowFocus } from '@vueuse/core';
import { Button } from '@/components/ui/button';

interface NavItem {
    href: string;
    label: string;
    icon: Component;
}

interface Props {
    navItems: NavItem[];
    whatsapp: string;
}

const props = defineProps<Props>();

const { open: openMoreDrawer, isOpen: isDrawerOpen } = useMoreDrawer();
const page = usePage();

// Separate state for "More" active styling
const isMoreActive = ref(false);

// Separate state for "Chat" active styling
const isChatActive = ref(false);

// WhatsApp link computed from props
const whatsappLink = `https://wa.me/${props.whatsapp}`;

// Delay before opening drawer (ms)
const OPEN_DELAY = 50;
// Delay before restoring nav state after drawer closes (ms)
const CLOSE_DELAY = 250;

// Reset chat active state when user returns to tab
const focused = useWindowFocus();
watch(focused, (isFocused) => {
    if (isFocused && isChatActive.value) {
        setTimeout(() => {
            isChatActive.value = false;
        }, CLOSE_DELAY);
    }
});

// Handle More button click with delay
const handleMoreClick = () => {
    // Immediately show "More" as active
    isMoreActive.value = true;
    
    // Open drawer after delay
    setTimeout(() => {
        openMoreDrawer();
    }, OPEN_DELAY);
};

// Watch drawer close to restore nav state after delay
watch(isDrawerOpen, (newVal) => {
    if (!newVal) {
        // Drawer closed, wait then restore nav state
        setTimeout(() => {
            isMoreActive.value = false;
        }, CLOSE_DELAY);
    }
});

// Check if current URL matches the nav item href
const isActive = (href: string) => {
    const currentUrl = page.url;
    if (href === '/') {
        return currentUrl === '/';
    }
    return currentUrl.startsWith(href);
};

// Nav item should show active style based on current route
const shouldShowNavActive = (href: string) => {
    return isActive(href);
};
</script>

<template>
    <header class="bg-background fixed z-50 w-full md:py-7">
        <div class="mx-auto max-w-screen-xl">
            
        </div>
        <div class="fixed bg-background z-50 w-full bottom-0 border-t pt-3 pb-2.5 md:py-0 md:static md:border-t-0">
            <div class="mx-auto px-6 md:max-w-screen-xl">
                <nav>
                    <ul class="flex items-center justify-between md:gap-6 md:justify-end">
                        <!-- Default Home link (mobile only) -->
                        <li
                            class="md:hidden"
                            :class="{ 'text-muted-foreground opacity-60': !shouldShowNavActive('/') }">
                            <Link href="/">
                                <div class="flex flex-col items-center gap-0.5">
                                    <Home class="w-6 h-6 stroke-[1.5px]" />
                                    <div class="text-[11px] font-medium">Home</div>
                                </div>
                            </Link>
                        </li>
                        <!-- Custom nav items from props -->
                        <li 
                            v-for="item in props.navItems" 
                            :key="item.href"
                            :class="{ 'text-muted-foreground opacity-60 md:opacity-100 md:hover:text-primary': !shouldShowNavActive(item.href) }"
                        >
                            <Link :href="item.href">
                                <div class="flex flex-col items-center gap-0.5">
                                    <component 
                                        :is="item.icon" 
                                        class="w-6 h-6 stroke-[1.5px] md:hidden"
                                    />
                                    <div class="text-[11px] font-medium md:text-base">
                                        {{ item.label }}
                                    </div>
                                </div>
                            </Link>
                        </li>
                        <li
                            :class="{ 'text-muted-foreground opacity-60 md:text-accent-foreground md:opacity-100': !isChatActive }"
                        >
                            <a :href="whatsappLink" target="_blank" class="flex flex-col items-center gap-0.5 duration-0 md:flex-row md:gap-2 md:border md:bg-background md:px-4 md:h-10 md:rounded-md md:hover:bg-accent md:hover:text-accent-foreground" @click="isChatActive = true">
                                <svg class="w-6 h-6 md:w-5 md:h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M72,104a32,32,0,0,1,32-32l16,32-12.32,18.47a48.19,48.19,0,0,0,25.85,25.85L152,136l32,16a32,32,0,0,1-32,32A80,80,0,0,1,72,104Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"/><path d="M79.93,211.11a96,96,0,1,0-35-35h0L32.42,213.46a8,8,0,0,0,10.12,10.12l37.39-12.47Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"/></svg>
                                <div class="text-[11px] font-medium md:text-sm">WhatsApp</div>
                            </a>
                        </li>
                        <!-- More button (mobile only) -->
                        <li
                            class="md:hidden"
                            :class="{ 'text-muted-foreground opacity-60': !isMoreActive }"
                        >
                            <button class="flex flex-col items-center gap-0.5 duration-0" @click="handleMoreClick">
                                <Menu class="w-6 h-6 stroke-[1.5px]"/>
                                <div class="text-[11px] font-medium">More</div>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
</template>