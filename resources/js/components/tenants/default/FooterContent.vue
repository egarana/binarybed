<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/vue3';
import { Facebook, Instagram, ShieldCheck, Youtube } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

export interface SocialLink {
    platform: 'facebook' | 'instagram' | 'youtube';
    url: string;
}

interface Props {
    socialLinks?: SocialLink[];
    address?: string;
    brandName?: string;
    compact?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    socialLinks: () => [
        { platform: 'facebook', url: 'https://facebook.com' },
        { platform: 'instagram', url: 'https://instagram.com' },
        { platform: 'youtube', url: 'https://youtube.com' },
    ],
    address: '123 Example Street, City Name, Country 12345',
    brandName: 'Your Brand Name',
});

// Auto-computed copyright year
const copyrightYear = computed(() => new Date().getFullYear());

// Map platform to icon component
const platformIcons: Record<SocialLink['platform'], Component> = {
    facebook: Facebook,
    instagram: Instagram,
    youtube: Youtube,
};
</script>

<template>
    <div class="px-6 md:px-0">
        <div class="md:flex md:items-center md:justify-between md:mx-auto md:max-w-screen-xl md:py-6 md:px-6">
            <ul :class="['py-6 md:py-0 md:flex md:items-center md:gap-3 md:text-sm', { 'text-sm': props.compact }]">
                <li class="py-2 md:py-0">
                    <Link href="/cancellation-policy" class="whitespace-nowrap">Cancellation Policy</Link>
                </li>
                <li class="py-2 md:py-0">
                    <Link href="/privacy-policy" class="whitespace-nowrap">Privacy Policy</Link>
                </li>
                <li class="py-2 md:py-0">
                    <Link href="/about" class="whitespace-nowrap">About</Link>
                </li>
                <li class="py-2 md:py-0">
                    <Link href="/faq" class="whitespace-nowrap">F.A.Q</Link>
                </li>
                <li class="py-2 md:py-0">
                    <Link href="/terms-and-conditions" class="whitespace-nowrap">Terms &amp; Conditions</Link>
                </li>
            </ul>
            <ul class="flex items-center gap-2 border-t pt-8 md:border-t-0 md:pt-0 md:flex-row-reverse">
                <li>
                    <img src="/mastercard.svg" alt="Mastercard" class="block w-auto h-3.5">
                </li>
                <li>
                    <img src="/visa.svg" alt="Visa" class="block w-auto h-3.5">
                </li>
                <li>
                    <Badge variant="outline" class="rounded-full bg-background">
                        <ShieldCheck class="text-green-600" />
                        Secure Payment
                    </Badge>
                </li>
            </ul>
        </div>
    </div>
    <div class="px-6 pt-4 pb-6 md:border-t md:py-0 md:px-0">
        <div class="md:mx-auto md:max-w-screen-xl md:py-5 md:px-6 md:flex md:justify-start md:items-center md:flex-row-reverse">
            <ul v-if="props.socialLinks.length > 0" class="flex items-center gap-5">
                <li v-for="link in props.socialLinks" :key="link.platform">
                    <a 
                        :href="link.url" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="text-muted-foreground hover:text-primary"
                    >
                        <component :is="platformIcons[link.platform]" class="w-4 h-4" />
                    </a>
                </li>
            </ul>
            <p class="text-xs text-balance text-muted-foreground mt-6 md:text-sm md:mt-0 md:me-auto">
                {{ copyrightYear }} {{ props.brandName }}, {{ props.address }}
            </p>
        </div>
    </div>
</template>