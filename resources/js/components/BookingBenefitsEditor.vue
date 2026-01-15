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
    Gift,
    Trash2
} from 'lucide-vue-next';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import Draggable from 'vuedraggable';

interface BookingBenefit {
    icon: string;
    title: string;
    description: string;
    _id?: string;
}

interface Props {
    modelValue: BookingBenefit[];
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: BookingBenefit[]): void;
}>();

const benefits = ref<BookingBenefit[]>([]);

// Initialize and watch modelValue
watch(() => props.modelValue, (newVal) => {
    const currentWithoutIds = benefits.value.map((sp) => {
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

    if (JSON.stringify(currentWithoutIds) !== JSON.stringify(newWithoutIds) || benefits.value.length !== newWithoutIds.length) {
        benefits.value = Array.isArray(newVal)
            ? newVal.map(sp => ({ ...sp, _id: sp._id || Math.random().toString(36).substring(7) }))
            : [];
    }
}, { immediate: true, deep: true });

// Emit changes
watch(benefits, (newVal) => {
    emit('update:modelValue', newVal);
}, { deep: true });

const addBenefit = () => {
    benefits.value.push({
        icon: '',
        title: '',
        description: '',
        _id: Math.random().toString(36).substring(7)
    });
};

const removeBenefit = (index: number) => {
    benefits.value.splice(index, 1);
};

const hasBenefits = computed(() => benefits.value.length > 0);
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <div>
                <Label class="flex items-center gap-1">Direct Booking Benefits <span class="text-muted-foreground">(Optional)</span></Label>
                <p class="text-xs text-muted-foreground mt-1">
                    Add exclusive perks for booking directly (e.g. Best Price Guarantee, Welcome Drink).
                </p>
            </div>
        </div>

        <Empty v-if="!hasBenefits" class="border border-dashed">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <Gift />
                </EmptyMedia>
                <EmptyTitle>No benefits added</EmptyTitle>
                <EmptyDescription>
                    Add perks that incentivize direct bookings.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <Button type="button" variant="outline" @click="addBenefit">
                    <PlusCircle /> Add benefit
                </Button>
            </EmptyContent>
        </Empty>

        <div v-else>
            <ItemGroup class="gap-4">
                <Draggable
                    v-model="benefits"
                    item-key="_id"
                    handle=".drag-handle"
                    class="space-y-4"
                    ghost-class="opacity-50"
                >
                    <template #item="{ element, index }">
                        <Item class="flex" variant="outline">
                            <ItemContent class="space-y-4 gap-0">
                                <FormField
                                    :id="`benefit-title-${index}`"
                                    label="Title"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g., Best Price Guarantee"
                                    v-model="element.title"
                                />

                                <FormField
                                    :id="`benefit-description-${index}`"
                                    label="Description"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g., Use code DIRECT for 10% off"
                                    v-model="element.description"
                                />

                                <div class="grid gap-2">
                                    <Label :for="`benefit-icon-${index}`" class="flex items-center gap-1">
                                        Icon (SVG)
                                        <span class="text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Textarea
                                        :id="`benefit-icon-${index}`"
                                        :name="`benefit-icon-${index}`"
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
                                <Button type="button" variant="outline" size="icon" @click="removeBenefit(index)">
                                    <Trash2 class="text-muted-foreground" />
                                </Button>
                            </ItemActions>
                        </Item>
                    </template>
                </Draggable>
            </ItemGroup>

            <Button class="mt-4" type="button" variant="outline" @click="addBenefit">
                <PlusCircle /> Add benefit
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>
