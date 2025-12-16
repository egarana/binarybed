<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Loader2,
    X,
} from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';

/**
 * Interface for select options
 */
export interface SelectOption {
    value: string;
    label: string;
    [key: string]: any; // Allow additional properties
}

/**
 * Props for DependentSelect
 */
interface Props {
    /** Current v-model value (the selected option) */
    modelValue?: SelectOption;
    
    /** Initial options (optionally passed from server) */
    options?: SelectOption[];
    
    /** Value of the dependency field that triggers fetch */
    dependsOn?: string | number | null;
    
    /** Function that returns fetch URL based on dependency value */
    fetchUrl: (dependencyValue: any) => string;
    
    /** Response key from server */
    responseKey: string;
    
    /** Label for the select */
    label?: string;
    
    /** Placeholder when nothing selected */
    placeholder?: string;
    
    /** Name for hidden input */
    name?: string;
    
    /** Tab index */
    tabindex?: number;
    
    /** Error message */
    error?: string;
    
    /** Whether field is required */
    required?: boolean;
    
    /** Whether select can be cleared */
    clearable?: boolean;
    
    /** Disabled state */
    disabled?: boolean;
    
    /** Message to show when no options available */
    emptyMessage?: string;
    
    /** Whether to fetch immediately on mount if dependsOn has value */
    fetchOnMount?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select an option',
    clearable: true,
    required: false,
    disabled: false,
    emptyMessage: 'No options available',
    fetchOnMount: true,
});

const emit = defineEmits<{
    'update:modelValue': [value: SelectOption | undefined];
}>();

// ========================================
// State
// ========================================

const items = ref<SelectOption[]>(props.options ?? []);
const isLoading = ref(false);
const selectedValue = ref<string>(props.modelValue?.value ?? '');

// ========================================
// Computed
// ========================================

const selectedOption = computed(() => {
    if (!selectedValue.value) return undefined;
    return items.value.find(item => item.value === selectedValue.value);
});

const inputName = computed(() => props.name ?? 'dependent_select');

const isDisabled = computed(() => {
    return props.disabled || !props.dependsOn || isLoading.value;
});

// ========================================
// Watchers
// ========================================

// Watch external model changes
watch(() => props.modelValue, (newVal) => {
    selectedValue.value = newVal?.value ?? '';
});

// Watch selected value and emit update
watch(selectedValue, (newVal) => {
    const option = items.value.find(item => item.value === newVal);
    emit('update:modelValue', option);
});

// Watch dependency value and fetch new data
watch(() => props.dependsOn, (newVal, oldVal) => {
    // Clear selection when dependency changes
    selectedValue.value = '';
    emit('update:modelValue', undefined);
    
    // Fetch new data if dependency has value
    if (newVal) {
        fetchItems();
    } else {
        items.value = [];
    }
}, { immediate: false });

// Watch props.options for changes
watch(() => props.options, (newOptions) => {
    if (newOptions) {
        items.value = newOptions;
    }
});

// ========================================
// Fetch Logic
// ========================================

const fetchItems = async () => {
    if (!props.dependsOn) return;
    
    isLoading.value = true;
    
    try {
        const url = props.fetchUrl(props.dependsOn);
        
        router.get(url, {}, {
            preserveScroll: true,
            preserveState: true,
            only: [props.responseKey],
            onSuccess: () => {
                const newItems = usePage().props[props.responseKey] as SelectOption[] ?? [];
                items.value = newItems;
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    } catch (error) {
        console.error('Error fetching items:', error);
        isLoading.value = false;
    }
};

// ========================================
// Actions
// ========================================

const clear = () => {
    selectedValue.value = '';
    emit('update:modelValue', undefined);
};

// ========================================
// Lifecycle
// ========================================

onMounted(() => {
    // Fetch initial data if dependsOn has value and fetchOnMount is true
    if (props.fetchOnMount && props.dependsOn && items.value.length === 0) {
        fetchItems();
    }
});
</script>

<template>
    <div class="grid gap-2">
        <!-- Label -->
        <Label v-if="label">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
            <span v-else class="text-muted-foreground">(Optional)</span>
        </Label>
        
        <div class="flex items-center gap-2">
            <!-- Select -->
            <Select v-model="selectedValue" :disabled="isDisabled">
                <SelectTrigger class="w-full">
                    <div class="flex items-center gap-2">
                        <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                        <SelectValue :placeholder="placeholder" />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <template v-if="items.length > 0">
                        <SelectItem 
                            v-for="item in items" 
                            :key="item.value" 
                            :value="item.value"
                        >
                            <slot name="option" :option="item">
                                {{ item.label }}
                            </slot>
                        </SelectItem>
                    </template>
                    <div v-else class="py-6 text-center text-sm text-muted-foreground">
                        {{ emptyMessage }}
                    </div>
                </SelectContent>
            </Select>
            
            <!-- Clear Button -->
            <Button
                v-if="clearable && selectedValue"
                type="button"
                variant="outline"
                size="icon"
                @click="clear"
                :disabled="disabled"
            >
                <X class="h-4 w-4" />
            </Button>
        </div>
        
        <!-- Hidden Input -->
        <input
            v-if="name"
            type="hidden"
            :name="name"
            :value="selectedValue"
        />
        
        <!-- Error Message -->
        <InputError v-if="error" :message="error" />
    </div>
</template>
