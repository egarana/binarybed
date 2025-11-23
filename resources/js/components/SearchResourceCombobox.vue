<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Check, ChevronsUpDown, Search, X } from 'lucide-vue-next';
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

export interface ComboboxOption {
    value: string;
    label: string;
}

const props = withDefaults(defineProps<{
    modelValue?: ComboboxOption;
    initialItems?: ComboboxOption[];
    fetchUrl: string | (() => string);
    responseKey: string;
    searchParam?: string;
    label?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    hiddenInputName?: string;
    clearable?: boolean;
    debounceMs?: number;
    id?: string;
    tabindex?: number;
}>(), {
    searchParam: 'search',
    placeholder: 'Select an option',
    searchPlaceholder: 'Search...',
    clearable: true,
    debounceMs: 300,
});

const emit = defineEmits<{
    'update:modelValue': [value: ComboboxOption | undefined];
}>();

const items = ref<ComboboxOption[]>(props.initialItems ?? []);
const searchTerm = ref('');

const fetchItems = debounce(() => {
    const url = typeof props.fetchUrl === 'function'
        ? props.fetchUrl()
        : props.fetchUrl;

    router.get(url, {
        [props.searchParam]: searchTerm.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: [props.responseKey],
        onSuccess: () => {
            const newItems = usePage().props[props.responseKey] as ComboboxOption[] ?? [];
            items.value = newItems;
        }
    });
}, props.debounceMs);

watch(searchTerm, (term) => {
    if (!term) {
        items.value = props.initialItems ?? [];
        return;
    }
    fetchItems();
});

// Sync initialItems when props change
watch(() => props.initialItems, (newItems) => {
    if (!searchTerm.value) {
        items.value = newItems ?? [];
    }
}, { deep: true });

const selected = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const clear = () => {
    emit('update:modelValue', undefined);
};

const inputId = computed(() => props.id ?? props.hiddenInputName ?? 'combobox');
</script>

<template>
    <div class="grid gap-2">
        <div v-if="label">
            <Label :for="inputId" class="inline-block">{{ label }}</Label>
        </div>
        <div class="flex items-center gap-2">
            <Combobox v-model="selected" class="w-full">
                <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button
                            :id="inputId"
                            :tabindex="tabindex"
                            type="button"
                            variant="outline"
                            class="justify-between w-full"
                            :class="{ 'font-normal text-muted-foreground': !selected?.value }"
                        >
                            {{ selected?.label ?? placeholder }}
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                </ComboboxAnchor>

                <ComboboxList align="start" class="w-full min-w-[200px]">
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

                    <ComboboxGroup :class="items.length < 1 ? 'p-0 border-none' : 'border-t'">
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
                    </ComboboxGroup>
                </ComboboxList>
            </Combobox>

            <Button
                v-if="clearable && selected"
                type="button"
                variant="outline"
                @click="clear"
            >
                Clear
                <X class="mt-0.5" />
            </Button>
        </div>
        <input
            v-if="hiddenInputName"
            type="hidden"
            :name="hiddenInputName"
            :value="selected?.value ?? ''"
        />
        <slot name="error" />
    </div>
</template>