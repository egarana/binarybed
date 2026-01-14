<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import FormField from '@/components/FormField.vue';
import { Textarea } from '@/components/ui/textarea';
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
    Sparkles,
    Trash2
} from 'lucide-vue-next';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import Draggable from 'vuedraggable';

interface SellingPoint {
    icon: string;
    title: string;
    description: string;
    _id?: string;
}

interface Props {
    modelValue: SellingPoint[];
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: SellingPoint[]): void;
}>();

const sellingPoints = ref<SellingPoint[]>([]);

// Initialize and watch modelValue
watch(() => props.modelValue, (newVal) => {
    const currentWithoutIds = sellingPoints.value.map((sp) => {
        const copy = { ...sp };
        delete copy._id;
        return copy;
    });
    const newWithoutIds = Array.isArray(newVal)
        ? newVal.map((sp) => {
              const copy = { ...sp };
              delete copy._id;
              return copy;
          })
        : [];

    if (JSON.stringify(currentWithoutIds) !== JSON.stringify(newWithoutIds) || sellingPoints.value.length !== newWithoutIds.length) {
        sellingPoints.value = Array.isArray(newVal)
            ? newVal.map(sp => ({ ...sp, _id: sp._id || Math.random().toString(36).substring(7) }))
            : [];
    }
}, { immediate: true, deep: true });

// Emit changes
watch(sellingPoints, (newVal) => {
    emit('update:modelValue', newVal);
}, { deep: true });

const addSellingPoint = () => {
    sellingPoints.value.push({
        icon: '',
        title: '',
        description: '',
        _id: Math.random().toString(36).substring(7)
    });
};

const removeSellingPoint = (index: number) => {
    sellingPoints.value.splice(index, 1);
};

const hasSellingPoints = computed(() => sellingPoints.value.length > 0);
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <div>
                <Label class="flex items-center gap-1">Selling Points <span class="text-muted-foreground">(Optional)</span></Label>
                <p class="text-xs text-muted-foreground mt-1">
                    Add unique selling points that differentiate this offering (e.g., Eco-Friendly, Expert Guide).
                </p>
            </div>
        </div>

        <Empty v-if="!hasSellingPoints" class="border border-dashed">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <Sparkles />
                </EmptyMedia>
                <EmptyTitle>No selling points added</EmptyTitle>
                <EmptyDescription>
                    Add unique features that make this offering stand out.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <Button type="button" variant="outline" @click="addSellingPoint">
                    <PlusCircle /> Add selling point
                </Button>
            </EmptyContent>
        </Empty>

        <div v-else>
            <ItemGroup class="gap-4">
                <Draggable
                    v-model="sellingPoints"
                    item-key="_id"
                    handle=".drag-handle"
                    class="space-y-4"
                    ghost-class="opacity-50"
                >
                    <template #item="{ element, index }">
                        <Item class="flex" variant="outline">
                            <ItemContent class="space-y-4">
                                <FormField
                                    :id="`selling-point-title-${index}`"
                                    label="Title"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g., Eco-Friendly"
                                    v-model="element.title"
                                />

                                <FormField
                                    :id="`selling-point-description-${index}`"
                                    label="Description"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g., Solar powered with rainwater harvesting"
                                    v-model="element.description"
                                />

                                <div class="grid gap-2">
                                    <Label :for="`selling-point-icon-${index}`" class="flex items-center gap-1">
                                        Icon (SVG)
                                        <span class="text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Textarea
                                        :id="`selling-point-icon-${index}`"
                                        :name="`selling-point-icon-${index}`"
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
                                <Button type="button" variant="outline" size="icon" @click="removeSellingPoint(index)">
                                    <Trash2 class="text-muted-foreground" />
                                </Button>
                            </ItemActions>
                        </Item>
                    </template>
                </Draggable>
            </ItemGroup>

            <Button class="mt-4" type="button" variant="outline" @click="addSellingPoint">
                <PlusCircle /> Add selling point
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>
