<script setup lang="ts">
import MainLayout from '@/layouts/tenants/default/MainLayout.vue';
import { KeyRound, Footprints } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Component } from 'vue';

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
</script>

<template>
    <MainLayout :title="props.title" :nav-items="navItems" :whatsapp="whatsapp">
        <template #cta>
            <Link href="/contact">
                <Button size="lg" class="hover:cursor-pointer">Contact Us</Button>
            </Link>
        </template>
        <slot />
    </MainLayout>
</template>