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
import FooterContent, { type SocialLink } from '@/components/tenants/default/FooterContent.vue';

interface Props {
    socialLinks?: SocialLink[];
    address?: string;
    brandName?: string;
}

const props = defineProps<Props>();

const { isOpen } = useMoreDrawer()
const isDesktop = useMediaQuery('(min-width: 768px)')

</script>

<template>
    <!-- Desktop: show content directly -->
    <footer v-if="isDesktop">
        <FooterContent 
            :social-links="props.socialLinks" 
            :address="props.address" 
            :brand-name="props.brandName" 
        />
    </footer>

    <!-- Mobile: show inside drawer -->
    <Drawer v-else v-model:open="isOpen">
        <DrawerContent>
            <DrawerHeader class="sr-only">
                <DrawerTitle>Footer Content</DrawerTitle>
                <DrawerDescription>Footer Content</DrawerDescription>
            </DrawerHeader>
            <FooterContent 
                :social-links="props.socialLinks" 
                :address="props.address" 
                :brand-name="props.brandName" 
            />
        </DrawerContent>
    </Drawer>
</template>
