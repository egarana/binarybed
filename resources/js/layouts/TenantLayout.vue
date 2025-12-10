<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { provide } from 'vue';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Tenant } from '@/types/tenant';

interface PageProps extends InertiaPageProps {
    tenant: Tenant;
}

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Welcome',
});

const page = usePage<PageProps>();
const tenant = page.props.tenant;

// Provide tenant data to all child components
provide('tenant', tenant);

</script>

<template>
    <Head>
        <title>{{ props.title }} - {{ tenant.name }}</title>
    </Head>
    <slot :tenant="tenant" />
</template>