<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import FormField from '@/components/FormField.vue';
import InputError from '@/components/InputError.vue';
import {
    Item,
    ItemContent,
    ItemActions,
    ItemGroup,
} from '@/components/ui/item';
import {
    PlusCircle,
    GripVertical,
    MapPin,
    Trash2
} from 'lucide-vue-next';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import Draggable from 'vuedraggable';

interface HighlightItem {
    text: string;
    _id?: string;
}

interface Props {
    modelValue: string[];
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

const highlights = ref<HighlightItem[]>([]);

// Initialize and watch modelValue
watch(() => props.modelValue, (newVal) => {
    const currentTexts = highlights.value.map(h => h.text);
    const newTexts = Array.isArray(newVal) ? newVal : [];

    if (JSON.stringify(currentTexts) !== JSON.stringify(newTexts) || highlights.value.length !== newTexts.length) {
        highlights.value = newTexts.map(text => ({
            text,
            _id: Math.random().toString(36).substring(7)
        }));
    }
}, { immediate: true, deep: true });

// Emit changes
watch(highlights, (newVal) => {
    emit('update:modelValue', newVal.map(h => h.text));
}, { deep: true });

const addHighlight = () => {
    highlights.value.push({
        text: '',
        _id: Math.random().toString(36).substring(7)
    });
};

const removeHighlight = (index: number) => {
    highlights.value.splice(index, 1);
};

const hasHighlights = computed(() => highlights.value.length > 0);
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <div>
                <Label class="flex items-center gap-1">Location Highlights <span class="text-muted-foreground">(Optional)</span></Label>
                <p class="text-xs text-muted-foreground mt-1">
                    Add nearby points of interest or features (e.g., "5 min walk to Lake Batur").
                </p>
            </div>
        </div>

        <Empty v-if="!hasHighlights" class="border border-dashed">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <MapPin />
                </EmptyMedia>
                <EmptyTitle>No highlights added</EmptyTitle>
                <EmptyDescription>
                    Add location highlights to help visitors find nearby attractions.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <Button type="button" variant="outline" @click="addHighlight">
                    <PlusCircle /> Add highlight
                </Button>
            </EmptyContent>
        </Empty>

        <div v-else>
            <ItemGroup class="gap-4">
                <Draggable
                    v-model="highlights"
                    item-key="_id"
                    handle=".drag-handle"
                    class="space-y-4"
                    ghost-class="opacity-50"
                >
                    <template #item="{ element, index }">
                        <Item class="flex items-end" variant="outline">
                            <ItemContent>
                                <FormField
                                    :id="`location-highlight-${index}`"
                                    :label="`Highlight ${index + 1}`"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g., 5 min walk to Lake Batur"
                                    v-model="element.text"
                                    :hide-label="true"
                                />
                            </ItemContent>
                            <ItemActions class="flex gap-2 shrink-0">
                                <Button variant="outline" size="icon" class="drag-handle cursor-move">
                                    <GripVertical class="text-muted-foreground"/>
                                </Button>
                                <Button type="button" variant="outline" size="icon" @click="removeHighlight(index)">
                                    <Trash2 class="text-muted-foreground" />
                                </Button>
                            </ItemActions>
                        </Item>
                    </template>
                </Draggable>
            </ItemGroup>

            <Button class="mt-4" type="button" variant="outline" @click="addHighlight">
                <PlusCircle /> Add highlight
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>
