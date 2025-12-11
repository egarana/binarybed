<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import { Label } from '@/components/ui/label';
import FormField from '@/components/FormField.vue';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Item,
    ItemActions,
    ItemContent,
    ItemGroup,
} from '@/components/ui/item';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import InputError from '@/components/InputError.vue';
import { PlusCircle, Trash2, Route } from 'lucide-vue-next';

type ResourceType = 'units' | 'activities';

interface Props {
    modelValue: Record<string, ResourceType>;
    error?: string;
}

interface RouteEntry {
    key: string;
    value: ResourceType;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => ({}),
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<string, ResourceType>): void;
}>();

// Resource type options (expandable later)
const resourceTypes: { value: ResourceType; label: string }[] = [
    { value: 'units', label: 'Units' },
    { value: 'activities', label: 'Activities' },
];

// Convert object to array for easier manipulation
const entries = ref<RouteEntry[]>([]);

// Track if we're currently emitting to prevent circular updates
let isEmitting = false;

// Initialize from modelValue
const initializeEntries = () => {
    const modelEntries = Object.entries(props.modelValue).map(([key, value]) => ({
        key,
        value: value as ResourceType,
    }));
    entries.value = modelEntries.length > 0 ? modelEntries : [];
};

// Initialize on mount
onMounted(() => {
    initializeEntries();
});

// Watch for external modelValue changes (but not when we're emitting)
watch(() => props.modelValue, (newValue) => {
    if (!isEmitting) {
        const newEntries = Object.entries(newValue).map(([key, value]) => ({
            key,
            value: value as ResourceType,
        }));
        
        // Only update if the structure has actually changed
        const currentKeys = entries.value.map(e => e.key).sort().join(',');
        const newKeys = newEntries.map(e => e.key).sort().join(',');
        
        if (currentKeys !== newKeys) {
            entries.value = newEntries;
        }
    }
}, { deep: true });

// Emit changes when entries change
watch(entries, () => {
    isEmitting = true;
    const result: Record<string, ResourceType> = {};
    entries.value.forEach((entry) => {
        if (entry.key.trim()) {
            result[entry.key.trim()] = entry.value;
        }
    });
    emit('update:modelValue', result);
    // Reset flag after a small delay to allow parent to update
    setTimeout(() => {
        isEmitting = false;
    }, 0);
}, { deep: true });

// Add new entry
const addEntry = () => {
    entries.value.push({ key: '', value: 'units' });
};

// Remove entry
const removeEntry = (index: number) => {
    entries.value.splice(index, 1);
};

// Format key (kebab-case, lowercase) and update entry
const updateEntryKey = (index: number, value: string | number) => {
    // Convert to kebab-case: lowercase, replace spaces with hyphens
    entries.value[index].key = String(value)
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/g, '');
};

// Check for duplicate keys
const hasDuplicateKey = (index: number) => {
    const currentKey = entries.value[index].key.trim();
    if (!currentKey) return false;
    return entries.value.some((entry, i) => i !== index && entry.key.trim() === currentKey);
};

// Computed: has any entries
const hasEntries = computed(() => entries.value.length > 0);
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <div>
                <Label>Resource Routes <span class="text-muted-foreground">(Optional)</span></Label>
                <p class="text-xs text-muted-foreground mt-1">
                    Configure URL routes that map to database resources. Route names become URL paths (e.g., 'accommodations' → /accommodations)
                </p>
            </div>
        </div>

        <!-- Empty State -->
        <Empty v-if="!hasEntries" class="border border-dashed">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <Route />
                </EmptyMedia>
                <EmptyTitle>No resource routes configured</EmptyTitle>
                <EmptyDescription>
                    Configure URL routes that map to database resources.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <Button type="button" variant="outline" @click="addEntry">
                    <PlusCircle /> Add route
                </Button>
            </EmptyContent>
        </Empty>

        <!-- Entries List with Item component -->
        <div v-else>
            <ItemGroup class="gap-4">
                <template v-for="(entry, index) in entries" :key="index">
                    <Item variant="outline" class="flex items-end">
                        <ItemContent class="flex-row gap-3">
                            <!-- Route Name Input -->
                            <FormField
                                :id="`route-key-${index}`"
                                label="Route Name"
                                type="text"
                                placeholder="e.g., accommodations"
                                :model-value="entry.key"
                                @update:model-value="updateEntryKey(index, $event)"
                                :error="hasDuplicateKey(index) ? 'Duplicate route name' : undefined"
                                class="flex-1"
                            />

                            <!-- Resource Type Select -->
                            <div class="grid gap-2 flex-1">
                                <Label :for="`route-type-${index}`">Resource Type</Label>
                                <Select v-model="entry.value" :name="`resource_routes[${entry.key}]`">
                                    <SelectTrigger :id="`route-type-${index}`">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in resourceTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </ItemContent>

                        <ItemActions>
                            <Button type="button" variant="outline" size="icon" @click="removeEntry(index)">
                                <Trash2 class="w-4 h-4 !text-muted-foreground" />
                            </Button>
                        </ItemActions>
                    </Item>
                </template>
            </ItemGroup>

            <!-- Add More Button -->
            <Button class="mt-4" type="button" variant="outline" @click="addEntry">
                <PlusCircle /> Add route
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>

