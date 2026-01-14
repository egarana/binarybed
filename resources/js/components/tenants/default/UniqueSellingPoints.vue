<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { type Resource } from '@/stores/useResourceStore';

interface Props {
    resource: Resource;
}

defineProps<Props>();
</script>

<template>
    <div v-if="resource.selling_points?.length" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div 
                v-for="(point, index) in resource.selling_points" 
                :key="index" 
                class="flex items-start gap-6"
            >
                <div class="h-10 w-10 shrink-0 border rounded-md flex items-center justify-center">
                    <Check v-if="!point.icon" class="w-5 h-5 text-muted-foreground stroke-[1.5px]" />
                    <div v-else v-html="point.icon" class="text-muted-foreground [&>svg]:size-5 [&>svg]:stroke-[1.5px]" />
                </div>
                <div>
                    <p class="font-medium">{{ point.title }}</p>
                    <p v-if="point.description" class="text-muted-foreground mt-1">{{ point.description }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
