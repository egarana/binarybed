<script setup lang="ts">
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import currenciesData from '@/data/currencies.json';

interface Currency {
    code: string;
    name: string;
    symbol: string;
}

interface Props {
    id: string;
    name?: string;
    label?: string;
    tabindex?: number;
    placeholder?: string;
    modelValue: string;
    error?: string;
    helpText?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select currency',
    label: 'Currency',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

// Load currencies from JSON
const currencies = currenciesData as Currency[];

// Computed with get/set for v-model
const currency = computed({
    get: () => props.modelValue,
    set: (val: string) => emit('update:modelValue', val),
});

// Format display label
const formatLabel = (curr: Currency) => `${curr.code} - ${curr.name}`;

// Use id as name if name not provided
const inputName = computed(() => props.name || props.id);
</script>

<template>
    <div class="grid gap-2">
        <Label v-if="label" :for="id">{{ label }}</Label>
        <Select v-model="currency" :disabled="disabled">
            <SelectTrigger :id="id" :tabindex="tabindex">
                <SelectValue :placeholder="placeholder" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="curr in currencies" :key="curr.code" :value="curr.code">
                    {{ formatLabel(curr) }}
                </SelectItem>
            </SelectContent>
        </Select>
        <!-- Hidden input for form submission -->
        <input type="hidden" :name="inputName" :value="currency" />
        <InputError :message="error" />
        <p v-if="helpText" class="text-xs text-muted-foreground">
            {{ helpText }}
        </p>
    </div>
</template>


