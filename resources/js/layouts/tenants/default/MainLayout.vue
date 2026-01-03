<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { provide, onMounted, watch } from 'vue';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Tenant } from '@/types/tenant';
import type { Component } from 'vue';

// Pinia stores
import { useTenantStore } from '@/stores/useTenantStore';
import { useResourceStore } from '@/stores/useResourceStore';
import Navbar from '@/components/tenants/default/Navbar.vue';
import Footer from '@/components/tenants/default/Footer.vue';

interface SharedResources {
    units: Array<{ id: number; name: string; slug: string; created_at: string }>;
    activities: Array<{ id: number; name: string; slug: string; created_at: string }>;
}

interface PageProps extends InertiaPageProps {
    tenant: Tenant;
    sharedResources?: SharedResources;
}

export interface NavItem {
    href: string;
    label: string;
    icon: Component;
}

interface Props {
    title?: string;
    navItems: NavItem[];
    whatsapp: string;
    logo?: Component;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Welcome',
});

const page = usePage<PageProps>();
const tenant = page.props.tenant;
const sharedResources = (page.props as any).sharedResources as SharedResources | null;

// Initialize Pinia stores
const tenantStore = useTenantStore();
const resourceStore = useResourceStore();

// Hydrate stores on mount
onMounted(() => {
    // Hydrate tenant store
    if (tenant && !tenantStore.isHydrated) {
        tenantStore.hydrate(tenant);
    }
    
    // Hydrate resource store from shared resources
    if (sharedResources && !resourceStore.isHydrated) {
        resourceStore.hydrate(sharedResources);
    }
});

// Watch for Inertia page changes (SPA navigation) and re-hydrate if needed
watch(() => page.props, (newProps) => {
    const newTenant = (newProps as any).tenant;
    const newSharedResources = (newProps as any).sharedResources;
    
    // Update tenant if changed
    if (newTenant && newTenant.id !== tenantStore.tenant?.id) {
        tenantStore.hydrate(newTenant);
    }
    
    // Update resources if available
    if (newSharedResources) {
        resourceStore.hydrate(newSharedResources);
    }
}, { deep: true });

// Provide tenant data to all child components (legacy support)
provide('tenant', tenant);
</script>

<template>
    <Head>
        <title>{{ props.title }} - {{ tenant.name }}</title>
    </Head>

    <Navbar :nav-items="props.navItems" :whatsapp="props.whatsapp" :logo="props.logo">
        <template #cta>
            <slot name="cta" />
        </template>
    </Navbar>
    
    <main class="px-6 max-w-screen-xl mx-auto">
        <slot :tenant="tenant" />
    </main>

    <Footer />
</template>