<script setup lang="ts">
import { MapPin } from 'lucide-vue-next';
import { computed } from 'vue';

interface LocationData {
    address?: string;
    subtitle?: string;
    map_url?: string;
    highlights?: string[];
}

interface Props {
    location?: LocationData | null;
}

const props = defineProps<Props>();

// Check if we have any location data to display
const hasLocationData = computed(() => {
    return props.location?.address || props.location?.subtitle || props.location?.highlights?.length;
});
</script>

<template>
    <div v-if="hasLocationData" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold">Location</h1>

        <div v-if="location?.address" class="flex flex-col items-start gap-4 mt-6 md:flex-row">
            <div class="h-10 w-10 shrink-0 border rounded-full flex items-center justify-center">
                <MapPin class="size-6 text-muted-foreground stroke-1" />
            </div>
            <div>
                <a 
                    v-if="location.map_url"
                    :href="location.map_url" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="font-medium underline hover:no-underline"
                >
                    {{ location.address }}
                </a>
                <span v-else class="font-medium">{{ location.address }}</span>
                <p v-if="location?.subtitle" class="text-muted-foreground mt-1">{{ location.subtitle }}</p>
            </div>
        </div>
        
        <ul v-if="location?.highlights?.length" class="flex flex-wrap gap-2 mt-4 md:gap-3">
            <li 
                v-for="(item, index) in location.highlights" 
                :key="index"
                class="px-3 py-1.5 bg-accent rounded-md text-xs text-muted-foreground md:text-sm"
            >
                {{ item }}
            </li>
        </ul>
    </div>
</template>
