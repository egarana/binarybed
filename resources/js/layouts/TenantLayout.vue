<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { provide, onMounted, watch } from 'vue';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Tenant } from '@/types/tenant';

// Pinia stores
import { useTenantStore } from '@/stores/useTenantStore';
import { useResourceStore } from '@/stores/useResourceStore';

// Dev tools - only import in development
import TenantDebugger from '@/components/dev/TenantDebugger.vue';

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

// Check if in development mode
const isDev = import.meta.env.DEV;
</script>

<template>
    <Head>
        <title>{{ props.title }} - {{ tenant.name }}</title>
    </Head>
    <slot :tenant="tenant" />
    
    <!-- Development Debugger -->
    <TenantDebugger v-if="isDev" :enabled="isDev" position="bottom-right" />
</template>