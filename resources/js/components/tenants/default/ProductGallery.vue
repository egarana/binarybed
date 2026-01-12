<script setup lang="ts">
import { computed, ref, onMounted, onUpdated, onUnmounted } from 'vue';
import { useMediaQuery } from '@vueuse/core';
import { Fancybox } from '@fancyapps/ui/dist/fancybox/fancybox.js';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import { Button } from '@/components/ui/button';
import type { MediaItem } from '@/stores/useResourceStore';
import { ImageOff } from 'lucide-vue-next';

interface Props {
    images: MediaItem[];
}

const props = defineProps<Props>();

// Responsive breakpoint
const isDesktop = useMediaQuery('(min-width: 768px)');

// Template ref for Fancybox binding
const galleryRef = ref<HTMLElement | null>(null);

// ============================================
// FANCYBOX LIFECYCLE
// ============================================
onMounted(() => {
    if (galleryRef.value) {
        Fancybox.bind(galleryRef.value, '[data-fancybox]', {
            // Options
        });
    }
});

onUpdated(() => {
    if (galleryRef.value) {
        Fancybox.unbind(galleryRef.value);
        Fancybox.bind(galleryRef.value, '[data-fancybox]', {
            // Options
        });
    }
});

onUnmounted(() => {
    if (galleryRef.value) {
        Fancybox.unbind(galleryRef.value);
        Fancybox.close();
    }
});

// ============================================
// COMPUTED
// ============================================

/** Total images available */
const totalImages = computed(() => props.images.length);

/** Images to display (max 5 for desktop grid) */
const displayImages = computed(() => props.images.slice(0, 5));

/** Number of images displayed */
const displayCount = computed(() => displayImages.value.length);

/** Primary/hero image */
const primaryImage = computed(() => props.images[0]?.original_url || null);

/** Show button logic:
 * Mobile: show if more than 1 image
 * Desktop: show only if more than 5 images
 */
const showButton = computed(() => {
    if (isDesktop.value) {
        return totalImages.value > 5;
    }
    return totalImages.value > 1;
});

/** Button text - always shows total count */
const buttonText = computed(() => `Show all ${totalImages.value} photos`);

/** Grid layout class based on image count (for desktop) */
const gridClass = computed(() => {
    switch (displayCount.value) {
        case 1: return 'gallery-grid-1';
        case 2: return 'gallery-grid-2';
        case 3: return 'gallery-grid-3';
        case 4: return 'gallery-grid-4';
        default: return 'gallery-grid-5'; // 5 or more
    }
});

/** Button size - lg on desktop, default on mobile */
const buttonSize = computed(() => isDesktop.value ? 'lg' : 'default');

/** Open fancybox programmatically */
const openGallery = () => {
    if (props.images.length > 0) {
        Fancybox.show(
            props.images.map(img => ({
                src: img.original_url,
                type: 'image',
            }))
        );
    }
};
</script>

<template>
    <div ref="galleryRef" class="relative">
        <!-- Empty State: No images -->
        <div 
            v-if="totalImages === 0" 
            class="aspect-[16/11] bg-accent flex items-center justify-center md:h-120 md:rounded-md"
        >
            <div class="text-center text-muted-foreground flex items-center gap-2">
                <ImageOff class="h-5 w-5 stroke-1 md:h-6 md:w-6" />
                <p>No images available</p>
            </div>
        </div>

        <!-- Mobile: Single Hero Image -->
        <div v-else-if="!isDesktop">
            <a 
                :href="primaryImage || ''" 
                data-fancybox="gallery"
                class="block aspect-[16/11] bg-muted-foreground bg-cover bg-center"
                :style="primaryImage ? { backgroundImage: `url(${primaryImage})` } : {}"
            ></a>
            <!-- Hidden links for all other images (for gallery navigation) -->
            <div class="hidden">
                <a 
                    v-for="img in images.slice(1)" 
                    :key="img.id"
                    :href="img.original_url"
                    data-fancybox="gallery"
                ></a>
            </div>
        </div>

        <!-- Desktop: Grid Layout (max 5 images) -->
        <div v-else :class="['grid gap-2.5 overflow-hidden h-120', gridClass]">
            <a 
                v-for="(img, index) in displayImages" 
                :key="img.id"
                :href="img.original_url"
                data-fancybox="gallery"
                :class="[
                    'overflow-hidden rounded-md cursor-pointer',
                    index === 0 ? 'gallery-main' : 'gallery-thumb'
                ]"
            >
                <div 
                    class="w-full h-full bg-muted-foreground bg-cover bg-center transition-transform duration-300 ease-in-out hover:scale-105"
                    :style="{ backgroundImage: `url(${img.original_url})` }"
                ></div>
            </a>
            <!-- Hidden links for remaining images (images 6+) -->
            <div v-if="totalImages > 5" class="hidden">
                <a 
                    v-for="img in images.slice(5)" 
                    :key="img.id"
                    :href="img.original_url"
                    data-fancybox="gallery"
                ></a>
            </div>
        </div>

        <!-- Button -->
        <Button 
            v-if="showButton"
            variant="outline" 
            :size="buttonSize"
            class="absolute bottom-6 right-6 z-10 hover:cursor-pointer md:left-6 md:right-auto"
            @click="openGallery"
        >
            <Images class="size-4" />
            {{ buttonText }}
        </Button>
    </div>
</template>

<style scoped>
/* ============================================
   GALLERY GRID LAYOUTS (Desktop only)
   Uses CSS Grid with explicit row heights
   ============================================ */

/* Base styles for all grid items */
.gallery-main,
.gallery-thumb {
    min-height: 0; /* Allow grid items to shrink */
}

/* 1 Image: Full width */
.gallery-grid-1 {
    grid-template-columns: 1fr;
}

/* 2 Images: Two equal columns */
.gallery-grid-2 {
    grid-template-columns: 1fr 1fr;
}

/* 3 Images: 1 large left (2 rows), 2 small right stacked */
.gallery-grid-3 {
    grid-template-columns: 3fr 2fr;
    grid-template-rows: 1fr 1fr;
    grid-template-areas:
        "main thumb1"
        "main thumb2";
}
.gallery-grid-3 .gallery-main {
    grid-area: main;
}
.gallery-grid-3 .gallery-thumb:nth-child(2) {
    grid-area: thumb1;
}
.gallery-grid-3 .gallery-thumb:nth-child(3) {
    grid-area: thumb2;
}

/* 4 Images: 1 large left (2 rows), 3 small right */
.gallery-grid-4 {
    grid-template-columns: 3fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    grid-template-areas:
        "main thumb1 thumb2"
        "main thumb3 thumb3";
}
.gallery-grid-4 .gallery-main {
    grid-area: main;
}
.gallery-grid-4 .gallery-thumb:nth-child(2) {
    grid-area: thumb1;
}
.gallery-grid-4 .gallery-thumb:nth-child(3) {
    grid-area: thumb2;
}
.gallery-grid-4 .gallery-thumb:nth-child(4) {
    grid-area: thumb3;
}

/* 5 Images: Airbnb-style - 1 large left, 4 small right in 2x2 grid */
.gallery-grid-5 {
    grid-template-columns: 2fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    grid-template-areas:
        "main thumb1 thumb2"
        "main thumb3 thumb4";
}
.gallery-grid-5 .gallery-main {
    grid-area: main;
}
.gallery-grid-5 .gallery-thumb:nth-child(2) {
    grid-area: thumb1;
}
.gallery-grid-5 .gallery-thumb:nth-child(3) {
    grid-area: thumb2;
}
.gallery-grid-5 .gallery-thumb:nth-child(4) {
    grid-area: thumb3;
}
.gallery-grid-5 .gallery-thumb:nth-child(5) {
    grid-area: thumb4;
}
</style>
