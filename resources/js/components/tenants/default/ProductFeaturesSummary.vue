<script setup lang="ts">
import { computed, ref } from 'vue';
import { useMediaQuery } from '@vueuse/core';
import { CheckCheck, Ban, Info } from 'lucide-vue-next';
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

// Categories configuration - single source of truth
const featureCategories = {
    exclusion: { icon: Ban, label: 'Not included' },
    requirement: { icon: Info, label: 'Requirements' },
    suggestion: { icon: CheckCheck, label: 'Suggestions' },
} as const;

type CategoryKey = keyof typeof featureCategories;

// Filter features by category
const getFeaturesByCategory = (category: string) => {
    return props.resource.features?.filter(f => f.pivot?.category === category) || [];
};

// Create categories data array for template iteration
const categoriesData = computed(() => {
    return Object.entries(featureCategories).map(([key, config]) => ({
        key: key as CategoryKey,
        features: getFeaturesByCategory(key),
        ...config,
    })).filter(cat => cat.features.length > 0);
});

// CheckCheck if any features exist
const hasFeatures = computed(() => categoriesData.value.length > 0);

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
    <div v-if="hasFeatures" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold">Good to know</h1>

        <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
            <div 
                v-for="category in categoriesData" 
                :key="category.key" 
                class="flex items-start gap-4"
            >
                <div class="h-10 w-10 shrink-0 bg-accent rounded-full flex items-center justify-center">
                    <component :is="category.icon" class="size-6 text-muted-foreground shrink-0 stroke-1" />
                </div>
                <div>
                    <p class="font-medium">{{ category.label }}</p>
                    <p class="mt-1 text-muted-foreground line-clamp-1">
                        <template v-for="(item, idx) in category.features.slice(0, 2)" :key="item.id">
                            <span :class="{ 'line-through': category.key === 'exclusion' }">{{ item.name }}</span><template v-if="idx < category.features.slice(0, 2).length - 1">, </template>
                        </template><template v-if="category.features.length > 2">, +{{ category.features.length - 2 }} more</template>
                    </p>
                </div>
            </div>
        </div>

        <!-- Show All Button - Responsive Modal -->
        <component :is="Modal.Root" v-model:open="isOpen">
            <component :is="Modal.Trigger" as-child>
                <Button variant="outline" class="mt-8" size="lg" @click="(e: MouseEvent) => (e.currentTarget as HTMLElement).blur()">
                    Show all {{ categoriesData.reduce((sum, cat) => sum + cat.features.length, 0) }} items
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
                        Good to know
                    </component>
                    <component :is="Modal.Description" class="text-base">
                        Important information about this experience
                    </component>
                </component>

                <div :class="isDesktop ? 'mt-4 overflow-y-auto flex-1' : 'overflow-y-auto px-6'">
                    <div 
                        v-for="category in categoriesData" 
                        :key="category.key" 
                        class="mb-0 md:mb-3"
                    >
                        <h1 :class="`font-semibold`">{{ category.label }}</h1>
                        <ul class="grid grid-cols-1 divide-y mt-3">
                            <li 
                                v-for="item in category.features" 
                                :key="item.id"
                                class="flex items-start gap-4 py-5 last:pb-8"
                            >
                                <!-- Icon with conditional slash for exclusions -->
                                <template v-if="!item.icon">
                                    <component :is="category.icon" class="size-6 text-muted-foreground shrink-0 stroke-1" />
                                </template>
                                <div v-else class="relative shrink-0">
                                    <div v-html="item.icon" class="text-muted-foreground [&>svg]:size-6 stroke-1" />
                                    <!-- Slash overlay for exclusion items -->
                                    <div v-if="category.key === 'exclusion'" class="absolute h-[5px] bg-background w-[130%] top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 rotate-45 flex items-center">
                                        <div class="h-[1px] w-full bg-muted-foreground"></div>
                                    </div>
                                </div>
                                <div>
                                    <p :class="['font-medium', { 'line-through': category.key === 'exclusion' }]">{{ item.name }}</p>
                                    <p v-if="item.description" class="text-muted-foreground">
                                        {{ item.description }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </component>
        </component>
    </div>
</template>
