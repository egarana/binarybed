<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/default.vue';
import { usePage } from '@inertiajs/vue3';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :title="'Home'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >

    </FrontEndLayout>
</template>