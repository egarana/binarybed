<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';

interface Props {
    id: string;
    label: string;
    type?: string;
    tabindex?: number;
    autocomplete?: string;
    placeholder?: string;
    modelValue: string | number;
    error?: string;
    helpText?: string;
}

withDefaults(defineProps<Props>(), {
    type: 'text',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">{{ label }}</Label>
        <Input
            :id="id"
            :name="id"
            :type="type"
            :tabindex="tabindex"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
        />
        <InputError :message="error" />
        <p v-if="helpText" class="text-xs text-muted-foreground">
            {{ helpText }}
        </p>
    </div>
</template>
