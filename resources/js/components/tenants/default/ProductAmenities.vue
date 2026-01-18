<script setup lang="ts">
import { computed, ref } from 'vue';
import { useMediaQuery } from '@vueuse/core';
import { CheckSquare } from 'lucide-vue-next';
import { type Resource } from '@/stores/useResourceStore';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Drawer,
    DrawerContent,
    DrawerHeader,
    DrawerTitle,
    DrawerDescription,
    DrawerTrigger,
} from '@/components/ui/drawer';

interface Props {
    resource: Resource;
}

const props = defineProps<Props>();

// Categories to include in amenities
const amenityCategories = ['amenity', 'facility', 'equipment', 'inclusion'];

// Filter features by amenity categories
const amenities = computed(() => {
    return props.resource.features?.filter(f => 
        amenityCategories.includes(f.pivot?.category || 'amenity')
    ) || [];
});

// First 6 for preview
const previewAmenities = computed(() => amenities.value.slice(0, 6));

// Check if we have more than preview limit
const hasMore = computed(() => amenities.value.length > 6);

// Modal open state
const isOpen = ref(false);

// Responsive modal: Dialog on desktop (≥768px), Drawer on mobile
const isDesktop = useMediaQuery('(min-width: 768px)');

const Modal = computed(() => ({
    Root: isDesktop.value ? Dialog : Drawer,
    Trigger: isDesktop.value ? DialogTrigger : DrawerTrigger,
    Content: isDesktop.value ? DialogContent : DrawerContent,
    Header: isDesktop.value ? DialogHeader : DrawerHeader,
    Title: isDesktop.value ? DialogTitle : DrawerTitle,
    Description: isDesktop.value ? DialogDescription : DrawerDescription,
}));
</script>

<template>
    <div v-if="amenities.length" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold">What you'll get</h1>
        
        <!-- Preview Grid -->
        <ul class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <li 
                v-for="amenity in previewAmenities" 
                :key="amenity.id"
                class="flex items-center gap-4"
            >
                <CheckSquare v-if="!amenity.icon" class="size-6 text-muted-foreground shrink-0 stroke-1" />
                <div v-else v-html="amenity.icon" class="text-muted-foreground [&>svg]:size-6 shrink-0 stroke-1" />
                <span class="line-clamp-1">{{ amenity.name }}</span>
            </li>
        </ul>

        <!-- Show All Button - Responsive Modal -->
        <component :is="Modal.Root" v-if="hasMore" v-model:open="isOpen">
            <component :is="Modal.Trigger" as-child>
                <Button variant="outline" class="mt-8" size="lg" @click="(e: MouseEvent) => (e.currentTarget as HTMLElement).blur()">
                    Show all {{ amenities.length }} items
                </Button>
            </component>
            <component 
                :is="Modal.Content" 
                @open-auto-focus="(e) => e.preventDefault()"
                :class="[
                    isDesktop ? 'sm:max-w-2xl max-h-[80vh] flex flex-col pb-0' : 'max-h-[80vh]',
                    !isDesktop && 'px-0 *:px-6'
                ]"
            >
                <component :is="Modal.Header" :class="isDesktop ? '' : 'p-6'">
                    <component :is="Modal.Title" class="text-lg">
                        What you'll get
                    </component>
                    <component :is="Modal.Description" class="text-base">
                        Complete list of everything included
                    </component>
                </component>

                <div :class="isDesktop ? 'mt-4 overflow-y-auto flex-1' : 'overflow-y-auto px-6'">
                    <ul class="grid grid-cols-1 divide-y">
                        <li 
                            v-for="amenity in amenities" 
                            :key="amenity.id"
                            class="flex items-start gap-4 py-5 last:pb-8 last:md:pb-11"
                        >
                            <CheckSquare v-if="!amenity.icon" class="size-6 text-muted-foreground shrink-0 stroke-1 mt-1" />
                            <div v-else v-html="amenity.icon" class="text-muted-foreground [&>svg]:size-6 shrink-0 stroke-1 mt-1" />
                            <div>
                                <p class="font-medium">{{ amenity.name }}</p>
                                <p v-if="amenity.description" class="text-muted-foreground">
                                    {{ amenity.description }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </component>
        </component>
    </div>
</template>
