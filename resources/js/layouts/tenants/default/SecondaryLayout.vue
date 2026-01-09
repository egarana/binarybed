<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { provide, onMounted, watch } from 'vue';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Tenant } from '@/types/tenant';

// Pinia stores
import { useTenantStore } from '@/stores/useTenantStore';
import { useResourceStore } from '@/stores/useResourceStore';
import SecondaryNavbar from '@/components/tenants/default/SecondaryNavbar.vue';
import FooterContent, { type SocialLink } from '@/components/tenants/default/FooterContent.vue';

interface SharedResources {
    units: Array<{ id: number; name: string; slug: string; created_at: string }>;
    activities: Array<{ id: number; name: string; slug: string; created_at: string }>;
}

interface PageProps extends InertiaPageProps {
    tenant: Tenant;
    sharedResources?: SharedResources;
}

interface Props {
    title?: string;
    // Footer props
    socialLinks?: SocialLink[];
    address?: string;
    brandName?: string;
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

    <SecondaryNavbar />
    
    <main>
        <slot :tenant="tenant" />
    </main>

    <footer class="bg-accent">
        <FooterContent 
            :social-links="props.socialLinks" 
            :address="props.address" 
            :brand-name="props.brandName"
            compact
        />
    </footer>
</template>