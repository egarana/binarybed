<script setup lang="ts">
import { computed, ref } from 'vue';
import { useMediaQuery } from '@vueuse/core';
import { CheckCircle, ChevronRightIcon, Star, Tags, X } from 'lucide-vue-next';
import { type Resource } from '@/stores/useResourceStore';
import { Item, ItemActions, ItemContent, ItemMedia, ItemTitle } from '@/components/ui/item';
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
import { useBenefitsVisibility } from '@/composables/useBenefitsVisibility';

interface Props {
    resource: Resource;
}

const props = defineProps<Props>();

// Benefits data from resource
const benefits = computed(() => props.resource.book_direct_benefits || []);

// Modal open state
const isOpen = ref(false);

// Shared visibility state (for close functionality)
const { isBenefitsHidden } = useBenefitsVisibility();

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
    <div v-if="benefits.length && !isBenefitsHidden" class="px-6 lg:px-0">
        <div class="relative">
            <button 
                @click.stop="isBenefitsHidden = true"
                class="absolute -top-1.5 -left-1.5 z-10 p-1 bg-background border rounded-full hover:bg-muted cursor-pointer"
                aria-label="Close benefits"
            >
                <X class="size-3 text-muted-foreground" />
            </button>
            <component :is="Modal.Root" v-model:open="isOpen">
                <component :is="Modal.Trigger" as-child>
                    <Item variant="outline" size="sm" class="w-full bg-background cursor-pointer group">
                        <ItemMedia>
                            <Tags class="size-5 text-muted-foreground stroke-[1.5px]" />
                        </ItemMedia>
                        <ItemContent>
                            <ItemTitle class="group-hover:underline">Booking direct benefits</ItemTitle>
                        </ItemContent>
                        <ItemActions>
                            <ChevronRightIcon class="size-4" />
                        </ItemActions>
                    </Item>
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
                            Booking Direct Benefits
                        </component>
                        <component :is="Modal.Description" class="text-base">
                            Save more and enjoy exclusive perks when booking directly
                        </component>
                    </component>
    
                    <div :class="isDesktop ? 'mt-4 overflow-y-auto flex-1' : 'overflow-y-auto px-6'">
                        <ul class="grid grid-cols-1 gap-4 md:gap-6">
                            <li 
                                v-for="(benefit, index) in benefits" 
                                :key="benefit._id || index"
                                class="flex items-start gap-4 py-4 px-4 border rounded-lg bg-lime-50 last:mb-8 md:p-0 md:border-0 md:bg-transparent"
                            >
                                <div class="h-10 w-10 shrink-0 rounded-md flex items-center justify-center bg-lime-600/80 mt-1">
                                    <CheckCircle v-if="!benefit.icon" class="w-5 h-5 text-white stroke-[1.5px]" />
                                    <div v-else v-html="benefit.icon" class="text-white [&>svg]:size-5 [&>svg]:stroke-[1.5px]" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ benefit.title }}</p>
                                    <p v-if="benefit.description" class="text-muted-foreground mt-0">{{ benefit.description }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </component>
            </component>
        </div>
    </div>
</template>
