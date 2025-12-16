<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { NumberField, NumberFieldInput } from '@/components/ui/number-field';
import InputError from '@/components/InputError.vue';

interface Props {
    id: string;
    label: string;
    tabindex?: number;
    placeholder?: string;
    modelValue: number;
    error?: string;
    helpText?: string;
    min?: number;
    max?: number;
}

withDefaults(defineProps<Props>(), {
    placeholder: '0',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">{{ label }}</Label>
        <input type="hidden" :name="id" :value="modelValue" />
        <NumberField 
            :id="id" 
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
            :min="min"
            :max="max"
            :format-options="{ style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 }"
        >
            <NumberFieldInput :tabindex="tabindex" :placeholder="placeholder" class="text-left px-3" />
        </NumberField>
        <InputError :message="error" />
        <p v-if="helpText" class="text-xs text-muted-foreground">
            {{ helpText }}
        </p>
    </div>
</template>
