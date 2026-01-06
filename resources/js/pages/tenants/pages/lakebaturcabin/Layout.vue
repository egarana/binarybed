<script setup lang="ts">
import PrimaryLayout from '@/layouts/tenants/default/PrimaryLayout.vue';
import { KeyRound, Footprints } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Component } from 'vue';
import type { SocialLink } from '@/components/tenants/default/FooterContent.vue';

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Lake Batur Cabin',
});

// Tenant-specific navigation items (Home is always included by default)
const navItems: { href: string; label: string; icon: Component }[] = [
    { href: '/cabins', label: 'Cabins', icon: KeyRound },
    { href: '/activities', label: 'Activities', icon: Footprints },
];

// Tenant-specific WhatsApp number
const whatsapp = '62881037990320';

// Tenant-specific footer data
const socialLinks: SocialLink[] = [
    { platform: 'facebook', url: 'https://facebook.com/lakebaturcabin' },
    { platform: 'instagram', url: 'https://instagram.com/lakebaturcabin' },
    { platform: 'youtube', url: 'https://youtube.com/@lakebaturcabin' },
];

const brandName = 'Lake Batur Cabin';
const address = 'Jalan Ulun Danu Songan A Kintamani Bangli Bali 80652 Indonesia';
</script>

<template>
    <PrimaryLayout 
        :title="props.title" 
        :nav-items="navItems" 
        :whatsapp="whatsapp"
        :social-links="socialLinks"
        :address="address"
        :brand-name="brandName"
    >
        <template #cta>
            <Link href="/contact">
                <Button size="lg" class="hover:cursor-pointer">Contact Us</Button>
            </Link>
        </template>
        <slot />
    </PrimaryLayout>
</template>