<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { provide, onMounted, watch } from 'vue';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Tenant } from '@/types/tenant';

// Pinia stores
import { useTenantStore } from '@/stores/useTenantStore';
import { useResourceStore } from '@/stores/useResourceStore';

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
    title: 'Booking',
});

const page = usePage<PageProps>();
const tenant = page.props.tenant;
const sharedResources = (page.props as any).sharedResources as SharedResources | null;

// Initialize Pinia stores
const tenantStore = useTenantStore();
const resourceStore = useResourceStore();

// Hydrate stores on mount
onMounted(() => {
    if (tenant && !tenantStore.isHydrated) {
        tenantStore.hydrate(tenant);
    }
    
    if (sharedResources && !resourceStore.isHydrated) {
        resourceStore.hydrate(sharedResources);
    }
});

// Watch for Inertia page changes
watch(() => page.props, (newProps) => {
    const newTenant = (newProps as any).tenant;
    const newSharedResources = (newProps as any).sharedResources;
    
    if (newTenant && newTenant.id !== tenantStore.tenant?.id) {
        tenantStore.hydrate(newTenant);
    }
    
    if (newSharedResources) {
        resourceStore.hydrate(newSharedResources);
    }
}, { deep: true });

// Provide tenant data to child components
provide('tenant', tenant);
</script>

<template>
    <Head>
        <title>{{ props.title }} - {{ tenant.name }}</title>
    </Head>

    <div class="min-h-screen bg-stone-50">
        <!-- No navbar here - page manages its own header -->
        <slot :tenant="tenant" />
    </div>
</template>
