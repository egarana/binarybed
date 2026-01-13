<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    Star,
    Trash2
} from 'lucide-vue-next';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import Draggable from 'vuedraggable';

interface Highlight {
    icon: string;
    label: string;
    _id?: string;
}

interface Props {
    modelValue: Highlight[];
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: Highlight[]): void;
}>();

const highlights = ref<Highlight[]>([]);

// Initialize and watch modelValue
watch(() => props.modelValue, (newVal) => {
    // We compare stringified values to avoid infinite loops, but we need to handle _id stability
    const currentWithoutIds = highlights.value.map((h) => {
        const copy = { ...h };
        delete copy._id;
        return copy;
    });
    const newWithoutIds = Array.isArray(newVal)
        ? newVal.map((h) => {
              const copy = { ...h };
              delete copy._id;
              return copy;
          })
        : [];

    if (JSON.stringify(currentWithoutIds) !== JSON.stringify(newWithoutIds) || highlights.value.length !== newWithoutIds.length) {
        highlights.value = Array.isArray(newVal)
            ? newVal.map(h => ({ ...h, _id: h._id || Math.random().toString(36).substring(7) }))
            : [];
    }
}, { immediate: true, deep: true });

// Emit changes
watch(highlights, (newVal) => {
    emit('update:modelValue', newVal);
}, { deep: true });

const addHighlight = () => {
    highlights.value.push({
        icon: '',
        label: '',
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
                <Label class="flex items-center gap-1">Highlights <span class="text-muted-foreground">(Optional)</span></Label>
                <p class="text-xs text-muted-foreground mt-1">
                    Add key features or highlights for this activity (e.g., Duration, Difficulty).
                </p>
            </div>
        </div>

        <Empty v-if="!hasHighlights" class="border border-dashed">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <Star />
                </EmptyMedia>
                <EmptyTitle>No highlights added</EmptyTitle>
                <EmptyDescription>
                    Add highlights like Duration, Difficulty, or Inclusions.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <Button type="button" variant="outline" @click="addHighlight">
                    <PlusCircle class="mr-2 h-4 w-4" /> Add highlight
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
                        <Item class="flex" variant="outline">
                            <ItemContent class="space-y-4">
                                <FormField
                                    :id="`highlight-label-${index}`"
                                    label="Label"
                                    type="text"
                                    autocomplete="organization"
                                    placeholder="e.g., 2 Hours"
                                    v-model="element.label"
                                />

                                <div class="grid gap-2">
                                    <Label :for="`highlight-icon-${index}`" class="flex items-center gap-1">
                                        Icon (SVG)
                                        <span class="text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Textarea
                                        :id="`highlight-icon-${index}`"
                                        :name="`highlight-icon-${index}`"
                                        v-model="element.icon"
                                        placeholder="Paste SVG code here..."
                                        rows="4"
                                    />
                                </div>
                            </ItemContent>
                            <ItemActions class="flex flex-col justify-between pt-[22px] shrink-0 self-stretch">
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
                <PlusCircle class="mr-2 h-4 w-4" /> Add highlight
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>
