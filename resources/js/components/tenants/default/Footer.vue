<script setup lang="ts">
import {
    Drawer,
    DrawerContent,
    DrawerDescription,
    DrawerHeader,
    DrawerTitle,
} from '@/components/ui/drawer';
import { useMoreDrawer } from '@/composables/useMoreDrawer';
import { useMediaQuery } from '@vueuse/core';
import FooterContent from '@/components/tenants/default/FooterContent.vue';

const { isOpen } = useMoreDrawer()
const isDesktop = useMediaQuery('(min-width: 1024px)')

</script>

<template>
    <!-- Desktop: show content directly -->
    <div v-if="isDesktop">
        <FooterContent />
    </div>

    <!-- Mobile: show inside drawer -->
    <Drawer v-else v-model:open="isOpen">
        <DrawerContent>
            <DrawerHeader>
                <DrawerTitle>Move Goal</DrawerTitle>
                <DrawerDescription>Set your daily activity goal.</DrawerDescription>
            </DrawerHeader>
            <FooterContent />
        </DrawerContent>
    </Drawer>
</template>
