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
    ScrollText,
    Trash2
} from 'lucide-vue-next';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import Draggable from 'vuedraggable';

interface Rule {
    icon: string;
    label: string;
    _id?: string;
}

interface Props {
    modelValue: Rule[];
    error?: string;
    label?: string;
    description?: string;
    emptyTitle?: string;
    emptyDescription?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    label: 'Rules',
    description: 'Add rules or guidelines that apply.',
    emptyTitle: 'No rules added',
    emptyDescription: 'Add rules like check-in time, restrictions, or guidelines.',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: Rule[]): void;
}>();

const rules = ref<Rule[]>([]);

// Initialize and watch modelValue
watch(() => props.modelValue, (newVal) => {
    // We compare stringified values to avoid infinite loops, but we need to handle _id stability
    const currentWithoutIds = rules.value.map((h) => {
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

    if (JSON.stringify(currentWithoutIds) !== JSON.stringify(newWithoutIds) || rules.value.length !== newWithoutIds.length) {
        rules.value = Array.isArray(newVal)
            ? newVal.map(h => ({ ...h, _id: h._id || Math.random().toString(36).substring(7) }))
            : [];
    }
}, { immediate: true, deep: true });

// Emit changes
watch(rules, (newVal) => {
    emit('update:modelValue', newVal);
}, { deep: true });

const addRule = () => {
    rules.value.push({
        icon: '',
        label: '',
        _id: Math.random().toString(36).substring(7)
    });
};

const removeRule = (index: number) => {
    rules.value.splice(index, 1);
};

const hasRules = computed(() => rules.value.length > 0);
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <div>
                <Label class="flex items-center gap-1">{{ label }} <span class="text-muted-foreground">(Optional)</span></Label>
                <p class="text-xs text-muted-foreground mt-1">
                    {{ description }}
                </p>
            </div>
        </div>

        <Empty v-if="!hasRules" class="border border-dashed">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <ScrollText />
                </EmptyMedia>
                <EmptyTitle>{{ emptyTitle }}</EmptyTitle>
                <EmptyDescription>
                    {{ emptyDescription }}
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <Button type="button" variant="outline" @click="addRule">
                    <PlusCircle /> Add rule
                </Button>
            </EmptyContent>
        </Empty>

        <div v-else>
            <ItemGroup class="gap-4">
                <Draggable
                    v-model="rules"
                    item-key="_id"
                    handle=".drag-handle"
                    class="space-y-4"
                    ghost-class="opacity-50"
                >
                    <template #item="{ element, index }">
                        <Item class="flex" variant="outline">
                            <ItemContent class="space-y-4 gap-0">
                                <FormField
                                    :id="`rule-label-${index}`"
                                    label="Label"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g., Check-in: 2:00 PM"
                                    v-model="element.label"
                                />

                                <div class="grid gap-2">
                                    <Label :for="`rule-icon-${index}`" class="flex items-center gap-1">
                                        Icon (SVG)
                                        <span class="text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Textarea
                                        :id="`rule-icon-${index}`"
                                        :name="`rule-icon-${index}`"
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
                                <Button type="button" variant="outline" size="icon" @click="removeRule(index)">
                                    <Trash2 class="text-muted-foreground" />
                                </Button>
                            </ItemActions>
                        </Item>
                    </template>
                </Draggable>
            </ItemGroup>

            <Button class="mt-4" type="button" variant="outline" @click="addRule">
                <PlusCircle /> Add rule
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>
