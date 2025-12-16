<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import Draggable from 'vuedraggable';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Item,
    ItemMedia,
    ItemContent,
    ItemTitle,
    ItemActions,
} from '@/components/ui/item';
import { 
    Check, 
    ChevronsUpDown, 
    GripVertical, 
    Search, 
    X 
} from 'lucide-vue-next';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger
} from "@/components/ui/combobox";
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue';
import { cn } from "@/lib/utils";

/**
 * Interface untuk option di combobox
 */
export interface ComboboxOption {
    value: string;
    label: string;
    icon?: any; // Icon component atau SVG string dari database
}

/**
 * Props untuk UnifiedCombobox
 */
interface Props {
    // Mode
    /** Mode combobox: single atau multiple */
    mode?: 'single' | 'multiple';
    
    // Model
    /** Value untuk single mode */
    modelValue?: ComboboxOption | ComboboxOption[];
    
    // Data
    /** Initial items/options */
    options?: ComboboxOption[];
    initialItems?: ComboboxOption[];
    
    // Fetch configuration
    /** URL untuk fetch data (bisa string atau function) */
    fetchUrl?: string | (() => string);
    /** Response key dari server */
    responseKey?: string;
    /** Nama parameter untuk search */
    searchParam?: string;
    /** Debounce delay dalam ms */
    debounceMs?: number;
    
    // UI Labels
    /** Label untuk combobox */
    label?: string;
    /** Placeholder saat belum ada yang dipilih */
    placeholder?: string;
    /** Placeholder untuk search input */
    searchPlaceholder?: string;
    
    // Form integration
    /** Nama untuk hidden input (single mode) atau hidden inputs (multiple mode) */
    name?: string;
    hiddenInputName?: string;
    /** ID untuk element */
    id?: string;
    /** Tab index */
    tabindex?: number;
    /** Error message */
    error?: string;
    /** Apakah field required */
    required?: boolean;
    
    // Features
    /** Apakah bisa di-clear (single mode) */
    clearable?: boolean;
    /** Apakah items bisa di-drag untuk reorder (multiple mode) */
    draggable?: boolean;
    /** Disabled state untuk form submission */
    disabled?: boolean;
    
    // Icons (untuk multiple mode)
    /** Icon general yang akan dipakai untuk semua item jika tidak ada icon per-item */
    generalIcon?: any;
    /** Fallback icon jika item tidak punya icon dan generalIcon tidak ada */
    defaultIcon?: any;
    /** Jika true, tampilkan defaultIcon sebagai fallback */
    showDefaultIcon?: boolean;
    /** Disable portal rendering - use when inside Dialog to prevent freeze */
    disablePortal?: boolean;
    /** Show/hide label - default true */
    showLabel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'single',
    searchParam: 'search',
    placeholder: 'Select an option',
    searchPlaceholder: 'Search...',
    clearable: true,
    draggable: true,
    debounceMs: 300,
    showDefaultIcon: false,
    required: false,
    disablePortal: false,
    disabled: false,
    showLabel: true,
});

const emit = defineEmits<{
    'update:modelValue': [value: ComboboxOption | ComboboxOption[] | undefined];
}>();

// ========================================
// State Management
// ========================================

// Gunakan options atau initialItems sebagai initial data
const initialData = computed(() => props.options ?? props.initialItems ?? []);

// Available items untuk ditampilkan di dropdown
const items = ref<ComboboxOption[]>(initialData.value);

// Search term
const searchTerm = ref('');

// Selected value berdasarkan mode
const selectedSingle = ref<ComboboxOption | undefined>(
    props.mode === 'single' && !Array.isArray(props.modelValue) 
        ? props.modelValue 
        : undefined
);

const selectedMultiple = ref<ComboboxOption[]>(
    props.mode === 'multiple' && Array.isArray(props.modelValue) 
        ? props.modelValue 
        : []
);

// Control dropdown open state
const isOpen = ref(false);

// ========================================
// Computed Properties
// ========================================

// Selected value yang di-bind ke Combobox (untuk single mode)
const selected = computed({
    get: () => props.mode === 'single' ? selectedSingle.value : selectedMultiple.value,
    set: (val) => {
        if (props.mode === 'single') {
            selectedSingle.value = val as ComboboxOption | undefined;
            emit('update:modelValue', val as ComboboxOption | undefined);
        } else {
            // Multiple mode dihandle manual via addItem/removeItem
        }
    }
});

// Input ID
const inputId = computed(() => 
    props.id ?? props.hiddenInputName ?? props.name ?? 'combobox'
);

// Name untuk hidden input
const inputName = computed(() => 
    props.hiddenInputName ?? props.name ?? 'combobox'
);

// Response key untuk fetch
const dataKey = computed(() => 
    props.responseKey ?? props.name ?? 'items'
);

// ========================================
// Watchers
// ========================================

// Watch external model changes
watch(() => props.modelValue, (newVal) => {
    if (props.mode === 'single' && !Array.isArray(newVal)) {
        selectedSingle.value = newVal;
    } else if (props.mode === 'multiple' && Array.isArray(newVal)) {
        selectedMultiple.value = newVal;
    }
});

// Watch multiple selection changes
watch(selectedMultiple, (newVal) => {
    if (props.mode === 'multiple') {
        emit('update:modelValue', newVal);
    }
}, { deep: true });

// Sync initialItems/options when props change
watch([() => props.initialItems, () => props.options], ([newInitial, newOptions]) => {
    if (!searchTerm.value) {
        items.value = newOptions ?? newInitial ?? [];
    }
}, { deep: true });

// ========================================
// Fetch Logic
// ========================================

const fetchItems = debounce(() => {
    if (!props.fetchUrl) return;
    
    const url = typeof props.fetchUrl === 'function'
        ? props.fetchUrl()
        : props.fetchUrl;

    router.get(url, {
        [props.searchParam]: searchTerm.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: [dataKey.value],
        onSuccess: () => {
            const newItems = usePage().props[dataKey.value] as ComboboxOption[] ?? [];
            items.value = newItems;
        }
    });
}, props.debounceMs);

// Watch search term
watch(searchTerm, (term) => {
    if (!term) {
        items.value = initialData.value;
        return;
    }
    if (props.fetchUrl) {
        fetchItems();
    }
});

// ========================================
// Actions
// ========================================

// Clear selection (single mode)
const clear = () => {
    if (props.mode === 'single') {
        selectedSingle.value = undefined;
        emit('update:modelValue', undefined);
    }
};

// Add item (multiple mode)
const addItem = (item: ComboboxOption) => {
    if (props.mode === 'multiple') {
        if (!selectedMultiple.value.some(i => i.value === item.value)) {
            selectedMultiple.value.push(item);
        }
        // Close dropdown after selection (like single mode)
        isOpen.value = false;
    }
};

// Remove item (multiple mode)
const removeItem = (item: ComboboxOption) => {
    if (props.mode === 'multiple') {
        selectedMultiple.value = selectedMultiple.value.filter(i => i.value !== item.value);
    }
};

// ========================================
// Icon Helpers (untuk multiple mode)
// ========================================

const getIconForItem = (item: ComboboxOption) => {
    // Priority 1: Icon dari item sendiri
    if (item.icon) {
        return item.icon;
    }
    
    // Priority 2: General icon
    if (props.generalIcon) {
        return props.generalIcon;
    }
    
    // Priority 3: Default icon (hanya jika showDefaultIcon = true)
    if (props.showDefaultIcon && props.defaultIcon) {
        return props.defaultIcon;
    }
    
    // Priority 4: Fallback ke Check icon jika showDefaultIcon = true
    if (props.showDefaultIcon) {
        return Check;
    }
    
    return null;
};

const isSvgString = (icon: any): boolean => {
    if (!icon) return false;
    return typeof icon === 'string' && icon.trim().startsWith('<svg');
};

const isValidComponent = (icon: any): boolean => {
    if (!icon) return false;
    if (typeof icon === 'string') return false;
    if (icon === null || icon === undefined) return false;
    
    if (typeof icon === 'object') {
        return true;
    }
    
    return false;
};

const getSafeIcon = (item: ComboboxOption) => {
    try {
        const icon = getIconForItem(item);
        if (!icon) return null;
        
        if (isSvgString(icon)) {
            return icon;
        }
        
        if (isValidComponent(icon)) {
            return icon;
        }
        
        return null;
    } catch (error) {
        console.warn('Error getting icon for item:', item, error);
        return null;
    }
};

// ========================================
// Display Helpers
// ========================================

const getButtonText = computed(() => {
    if (props.mode === 'single') {
        return selectedSingle.value?.label ?? props.placeholder;
    } else {
        if (selectedMultiple.value.length > 0) {
            const count = selectedMultiple.value.length;
            return `${count} ${count > 1 ? 'items' : 'item'} selected`;
        }
        return props.placeholder;
    }
});

const hasSelection = computed(() => {
    if (props.mode === 'single') {
        return !!selectedSingle.value?.value;
    } else {
        return selectedMultiple.value.length > 0;
    }
});

// Method to programmatically open the dropdown
const open = () => {
    isOpen.value = true;
};

// Expose methods for parent component
defineExpose({
    open,
    isOpen,
});
</script>

<template>
    <div class="grid gap-2">
        <!-- Label -->
        <Label v-if="label && showLabel" :for="inputId" class="inline-block">
            {{ label }}
            <span v-if="!required" class="text-muted-foreground">(Optional)</span>
        </Label>
        
        <div class="flex items-center gap-2">
            <!-- Combobox -->
            <Combobox :disabled="disabled" v-model="selected" v-model:open="isOpen" class="w-full">
                <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button
                            :id="inputId"
                            :tabindex="tabindex"
                            type="button"
                            variant="outline"
                            class="justify-between w-full"
                            :class="{ 'font-normal text-muted-foreground': !hasSelection }"
                        >
                            {{ getButtonText }}
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                </ComboboxAnchor>

                <!-- Dropdown List -->
                <ComboboxList :avoid-collisions="false" :disable-portal="disablePortal" align="start" class="w-full min-w-[200px]">
                    <!-- Search Input -->
                    <div class="relative w-full max-w-sm items-center combobox-input-wrapper">
                        <ComboboxInput
                            v-model="searchTerm"
                            :placeholder="searchPlaceholder"
                            class="combobox-search-input"
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>

                    <!-- Items -->
                    <ComboboxGroup :class="items.length < 1 ? 'p-0 border-none' : 'border-t'">
                        <!-- Single Mode -->
                        <template v-if="mode === 'single'">
                            <ComboboxItem
                                v-for="item in items"
                                :key="item.value"
                                :value="item"
                            >
                                {{ item.label }}
                                <ComboboxItemIndicator>
                                    <Check :class="cn('ml-auto h-4 w-4')" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </template>
                        
                        <!-- Multiple Mode -->
                        <template v-else>
                            <ComboboxItem
                                v-for="item in items"
                                :key="item.value"
                                :value="item"
                                @select.prevent="() => addItem(item)"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ item.label }}</span>
                                </div>
                                <ComboboxItemIndicator 
                                    v-if="selectedMultiple.some(i => i.value === item.value)"
                                >
                                    <Check :class="cn('ml-auto h-4 w-4')" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </template>
                    </ComboboxGroup>
                </ComboboxList>
            </Combobox>

            <!-- Clear Button (Single Mode) -->
            <Button
                v-if="mode === 'single' && clearable && selectedSingle"
                type="button"
                variant="outline"
                @click="clear"
                :disabled="disabled"
            >
                Clear
                <X class="mt-0.5" />
            </Button>
        </div>
        
        <!-- Selected Items Display (Multiple Mode) -->
        <template v-if="mode === 'multiple' && selectedMultiple.length > 0">
            <!-- With Draggable -->
            <Draggable
                v-if="draggable"
                v-model="selectedMultiple"
                item-key="value"
                class="flex items-center flex-wrap gap-2 mt-2 text-sm"
                ghost-class="opacity-35"
                handle=".drag-handle"
            >
                <template #item="{ element, index }">
                    <div class="group/drag drag-handle cursor-move">
                        <Item variant="outline" size="sm">
                            <ItemMedia>
                                <GripVertical 
                                    class="size-4 opacity-30 group-hover/drag:opacity-100 text-muted-foreground" 
                                />
                            </ItemMedia>
                            <ItemMedia v-if="getSafeIcon(element)">
                                <!-- SVG String -->
                                <span 
                                    v-if="isSvgString(getSafeIcon(element))"
                                    v-html="getSafeIcon(element)"
                                    class="size-4 [&>svg]:size-4 [&>svg]:stroke-current"
                                />
                                <!-- Component Icon -->
                                <component 
                                    v-else-if="isValidComponent(getSafeIcon(element))"
                                    :is="getSafeIcon(element)" 
                                    class="size-4 stroke-1" 
                                />
                            </ItemMedia>
                            <ItemContent>
                                <ItemTitle>{{ element.label }}</ItemTitle>
                            </ItemContent>
                            <ItemActions>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="size-6 opacity-50 hover:opacity-100"
                                    @click="removeItem(element)"
                                >
                                    <X class="size-4" />
                                </Button>
                            </ItemActions>
                            <input 
                                type="hidden" 
                                :name="`${inputName}[${index}]`" 
                                :value="element.value" 
                            />
                        </Item>
                    </div>
                </template>
            </Draggable>
            
            <!-- Without Draggable -->
            <div 
                v-else 
                class="flex items-center flex-wrap gap-2 mt-2 text-sm"
            >
                <Item 
                    v-for="(element, index) in selectedMultiple" 
                    :key="element.value"
                    variant="outline" 
                    size="sm"
                >
                    <ItemMedia v-if="getSafeIcon(element)">
                        <!-- SVG String -->
                        <span 
                            v-if="isSvgString(getSafeIcon(element))"
                            v-html="getSafeIcon(element)"
                            class="size-4 [&>svg]:size-4 [&>svg]:stroke-current"
                        />
                        <!-- Component Icon -->
                        <component 
                            v-else-if="isValidComponent(getSafeIcon(element))"
                            :is="getSafeIcon(element)" 
                            class="size-4 stroke-1" 
                        />
                    </ItemMedia>
                    <ItemContent>
                        <ItemTitle>{{ element.label }}</ItemTitle>
                    </ItemContent>
                    <ItemActions>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="size-6 opacity-50 hover:opacity-100"
                            @click="removeItem(element)"
                        >
                            <X class="size-4" />
                        </Button>
                    </ItemActions>
                    <input 
                        type="hidden" 
                        :name="`${inputName}[${index}]`" 
                        :value="element.value" 
                    />
                </Item>
            </div>
        </template>
        
        <!-- Hidden input for empty array (multiple mode) -->
        <!-- This ensures that when all items are removed, server receives features=[] -->
        <input
            v-if="mode === 'multiple' && selectedMultiple.length === 0 && inputName"
            type="hidden"
            :name="`${inputName}[]`"
            value=""
        />
        
        <!-- Hidden Input (Single Mode) -->
        <input
            v-if="mode === 'single' && inputName"
            type="hidden"
            :name="inputName"
            :value="selectedSingle?.value ?? ''"
        />
        
        <!-- Error Message -->
        <InputError v-if="error" :message="error" />
        <slot name="error" />
    </div>
</template>