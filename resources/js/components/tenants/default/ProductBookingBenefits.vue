<script setup lang="ts">
import { computed } from 'vue';
import { type Resource } from '@/stores/useResourceStore';

interface Props {
    resource: Resource;
}

const props = defineProps<Props>();

const benefits = computed(() => props.resource.book_direct_benefits || []);
</script>

<template>
    <div v-if="benefits.length > 0" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold">Direct booking benefits</h1>

        <div>
            
        </div>
        <!-- <div class="flex items-center justify-between">
            <h2 class="text-xl font-medium text-stone-900">Book direct & get more</h2>
            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Exclusive</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div 
                v-for="(perk, index) in benefits" 
                :key="index"
                class="flex items-start gap-3 p-4 bg-green-50 rounded-xl border border-green-100"
            >
                <div v-if="perk.icon" class="p-2 bg-green-100 rounded-lg">
                    <div v-html="perk.icon" class="w-5 h-5 text-green-600 [&>svg]:w-full [&>svg]:h-full"></div>
                </div>
                <div>
                    <h3 class="font-medium text-stone-900">{{ perk.title }}</h3>
                    <p class="text-sm text-stone-500">{{ perk.description }}</p>
                </div>
            </div>
        </div> -->
    </div>
</template>
