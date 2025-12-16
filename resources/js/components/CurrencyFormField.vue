<script setup lang="ts">
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { computed } from 'vue';

interface Props {
    id: string;
    label: string;
    tabindex?: number;
    placeholder?: string;
    modelValue: string;
    error?: string;
    helpText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'IDR',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

// Computed with get/set for v-model
const currency = computed({
    get: () => props.modelValue,
    set: (val: string) => {
        // Only uppercase letters, max 3 chars
        const cleaned = val.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
        emit('update:modelValue', cleaned);
    }
});
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">{{ label }}</Label>
        <input
            :id="id"
            :name="id"
            type="text"
            :tabindex="tabindex"
            :placeholder="placeholder"
            v-model="currency"
            maxlength="3"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm uppercase"
        />
        <InputError :message="error" />
        <p v-if="helpText" class="text-xs text-muted-foreground">
            {{ helpText }}
        </p>
    </div>
</template>
