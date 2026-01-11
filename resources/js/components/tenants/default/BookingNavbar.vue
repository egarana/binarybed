<script setup lang="ts">
import { type Component } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, X, Check } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Step {
    number: number;
    title: string;
    icon?: Component;
}

interface Props {
    logo?: Component;
    currentStep?: number;
    steps?: Step[];
    showBackButton?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    currentStep: 1,
    steps: () => [],
    showBackButton: true,
});

const emit = defineEmits<{
    back: [];
    close: [];
}>();

function handleBack() {
    emit('back');
}

function handleClose() {
    emit('close');
}
</script>

<template>
    <header class="sticky top-0 z-50 bg-white border-b border-stone-100">
        <div class="max-w-lg mx-auto flex items-center justify-between p-4">
            <!-- Back button -->
            <button 
                v-if="props.showBackButton && props.currentStep > 1" 
                @click="handleBack" 
                class="p-2 -ml-2 hover:bg-stone-100 rounded-full transition-colors"
                aria-label="Go back"
            >
                <ChevronLeft class="w-5 h-5" />
            </button>
            <div v-else class="w-9"></div>
            
            <!-- Step indicator - numbered circles (if steps provided) -->
            <div v-if="props.steps.length > 0" class="flex items-center gap-1">
                <template v-for="step in props.steps" :key="step.number">
                    <div 
                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all duration-300"
                        :class="[
                            step.number < props.currentStep ? 'bg-emerald-500 text-white' : '',
                            step.number === props.currentStep ? 'bg-stone-900 text-white' : '',
                            step.number > props.currentStep ? 'bg-stone-100 text-stone-400' : ''
                        ]"
                    >
                        <Check v-if="step.number < props.currentStep" class="w-4 h-4" />
                        <span v-else>{{ step.number }}</span>
                    </div>
                    <div 
                        v-if="step.number < props.steps.length"
                        class="w-6 h-0.5 transition-colors duration-300"
                        :class="step.number < props.currentStep ? 'bg-emerald-500' : 'bg-stone-200'"
                    ></div>
                </template>
            </div>
            
            <!-- Logo (if no steps) -->
            <Link v-else href="/" class="flex-1 flex justify-center">
                <component :is="props.logo || AppLogoIcon" class="h-8 w-auto" />
            </Link>
            
            <!-- Close button -->
            <button 
                @click="handleClose" 
                class="p-2 -mr-2 hover:bg-stone-100 rounded-full transition-colors"
                aria-label="Close"
            >
                <X class="w-5 h-5" />
            </button>
        </div>
    </header>
</template>
