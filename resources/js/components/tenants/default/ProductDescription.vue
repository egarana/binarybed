<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { type Resource } from '@/stores/useResourceStore';

interface Props {
    resource: Resource;
    resourceType: 'units' | 'activities';
}

const props = defineProps<Props>();

// Description expansion state
const isDescriptionExpanded = ref(false);

// Word limit for truncation
const WORD_LIMIT = 50;

// Check if description needs truncation
const needsTruncation = computed(() => {
    if (!props.resource.description) return false;
    const words = props.resource.description.trim().split(/\s+/);
    return words.length > WORD_LIMIT;
});

const truncatedDescription = computed(() => {
    if (!props.resource.description) return '';
    if (isDescriptionExpanded.value) {
        return props.resource.description;
    }
    
    // Truncate to word limit
    const words = props.resource.description.trim().split(/\s+/);
    if (words.length <= WORD_LIMIT) {
        return props.resource.description;
    }
    
    return words.slice(0, WORD_LIMIT).join(' ') + '...';
});
</script>

<template>
    <div v-if="resource.description" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold">{{ resourceType === 'activities' ? 'About this experience' : 'About this place' }}</h1>
        <p class="whitespace-pre-line mt-4">
            {{ truncatedDescription }}
        </p>
        <button 
            v-if="needsTruncation"
            @click="isDescriptionExpanded = !isDescriptionExpanded"
            class="flex items-center gap-1.5 underline hover:no-underline mt-6 font-medium"
        >
            {{ isDescriptionExpanded ? 'Show less' : 'Read more' }}
            <ChevronDown 
                class="w-4 h-4" 
                :class="{ 'rotate-180': isDescriptionExpanded }"
            />
        </button>
    </div>
</template>
