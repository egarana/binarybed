<script setup lang="ts">
import { computed } from 'vue';
import { type Resource } from '@/stores/useResourceStore';
import { CheckSquare } from 'lucide-vue-next';

interface Props {
    resource: Resource;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'House rules' // Default title, can be overridden for Activities
});

const rules = computed(() => props.resource.rules || []);
</script>

<template>
    <div v-if="rules.length > 0" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold">{{ title }}</h1>

        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <li 
                v-for="(rule, index) in rules"
                :key="rule._id || index"
                class="flex items-center gap-4"
            >
                <CheckSquare v-if="!rule.icon" class="size-6 text-muted-foreground shrink-0 stroke-1" />
                <div v-else v-html="rule.icon" class="text-muted-foreground [&>svg]:size-6 shrink-0 stroke-1" />
                <span>{{ rule.label }}</span>
            </li>
        </ul>
    </div>
</template>
