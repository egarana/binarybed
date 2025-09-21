<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Check, CheckIcon, ChevronsUpDown, GripVertical, Search, X } from "lucide-vue-next"
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger
} from "@/components/ui/combobox"
import { cn } from "@/lib/utils"
import { debounce } from 'lodash-es'
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue';
import NumberField from '@/components/ui/number-field/NumberField.vue';
import NumberFieldContent from '@/components/ui/number-field/NumberFieldContent.vue';
import NumberFieldInput from '@/components/ui/number-field/NumberFieldInput.vue';
import draggable from 'vuedraggable'

interface PropertyOption {
    value: string;
    label: string;
}

interface FeatureOption {
    value: string;
    name: string;
    icon: string;
}

const properties = ref<PropertyOption[]>([])
const features = ref<FeatureOption[]>([])
const comboboxSearchTerm = ref('')
const comboboxFeatureSearchTerm = ref('')
const open = ref(false)

const selectedProperty = ref<PropertyOption | undefined>()

const form = useForm({
    name: '',
    standard_rate: 0,
    property: null as PropertyOption | null,
    features: [] as FeatureOption[],
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Units',
        href: '/units',
    },
    {
        title: 'Add Unit',
        href: '/units/create',
    },
];

const submit = () => {
    form.post(route('units.store'), {
        preserveScroll: false,
        onSuccess: () => {
            toast('Unit created', {
                description: 'The units has been created successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: () => {
            toast('Error creating unit', {
                description: 'Something went wrong, please try again',
                action: {
                    label: 'Close',
                },
            })
        },
    });
};

const handleKeydown = (e: KeyboardEvent) => {
    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    const isSaveShortcut = (isMac && e.metaKey && e.key === 's') || (!isMac && e.ctrlKey && e.key === 's');

    if (isSaveShortcut) {
        e.preventDefault(); // prevent browser's default "save" behavior
        submit();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const fetchComboboxData = debounce(() => {
    router.get(route('units.create'), {
        search: comboboxSearchTerm.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['properties'],
        onSuccess: () => {
            const newProps = usePage().props.properties as PropertyOption[] ?? []
            properties.value = newProps
        }
    })
}, 300)

watch(comboboxSearchTerm, (term) => {
    if (!term) {
        properties.value = []
        return
    }

    fetchComboboxData()
})

watch(selectedProperty, (newVal) => {
    form.property = newVal ?? null
})

const fetchFeatureOptions = debounce(() => {
    router.get(route('units.create'), {
        search: comboboxFeatureSearchTerm.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['features'],
        onSuccess: () => {
            const pageProps = usePage().props as { features?: FeatureOption[] }
            features.value = pageProps.features ?? []
        },
    })
}, 300)

watch(comboboxFeatureSearchTerm, (term) => {
    if (!term) {
        features.value = []
        return
    }

    fetchFeatureOptions()
})

const removeFeature = (feature: FeatureOption) => {
    form.features = form.features.filter(f => f.value !== feature.value)
}
</script>

<template>
    <Head title="Add Unit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex flex-col space-y-6 min-h-full">
                <HeadingSmall title="Add unit" description="Add a new unit to include it in your management system" />

                <Separator />
    
                <form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
                    <div class="grid gap-2">
                        <Label>Property</Label>
                        <div class="mt-1 flex items-center gap-2">
                            <Combobox v-model="selectedProperty" class="w-full">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="justify-between w-full"
                                            :class="{ 'font-normal text-muted-foreground': !selectedProperty?.value }"
                                        >
                                            {{ selectedProperty?.label ?? 'Select a property to assign' }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
    
                                <ComboboxList align="start" class="w-full min-w-[200px]">
                                    <div class="relative w-full max-w-sm items-center combobox-input-wrapper">
                                        <ComboboxInput
                                            v-model="comboboxSearchTerm"
                                            placeholder="Search property..."
                                        />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
    
                                    <!-- <ComboboxEmpty>
                                        No properties found.
                                    </ComboboxEmpty> -->
    
                                    <ComboboxGroup :class="properties.length < 1 ? 'p-0 border-none' : 'border-t'">
                                        <ComboboxItem
                                            v-for="(property, index) in properties"
                                            :key="property.value"
                                            :value="property"
                                        >
                                            {{ property.label }}
        
                                            <ComboboxItemIndicator>
                                                <Check :class="cn('ml-auto h-4 w-4')" />
                                            </ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <Button type="button" variant="outline" @click="selectedProperty = undefined">
                                Clear
                                <X class="mt-0.5"/>
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" autocomplete="name" placeholder="Unit name (e.g. Whole Villa, Deluxe Room)" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="standardRate">Standard rate</Label>
                        <NumberField 
                            id="standardRate" 
                            :default-value="0" 
                            :min="0" 
                            class="mt-1"
                            :format-options="{
                                style: 'currency',
                                currency: 'IDR',
                                currencyDisplay: 'narrowSymbol',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }"
                            v-model="form.standard_rate"
                        >
                            <NumberFieldContent>
                                <NumberFieldInput class="text-start px-3 text-sm" />
                            </NumberFieldContent>
                        </NumberField>
                        <InputError :message="form.errors.standard_rate" />
                    </div>

                    <div class="grid">
                        <Label for="features">Features</Label>
                        <Combobox v-model="form.features" v-model:open="open" class="mt-3 combobox-tags-input-wrapper">
                            <ComboboxAnchor as-child class="rounded-none h-auto !p-0 !block !w-full">
                                <ComboboxInput 
                                    id="features"
                                    v-model="comboboxFeatureSearchTerm"
                                    placeholder="Type to search and add features"
                                />
                            </ComboboxAnchor>

                            <ComboboxList align="start" :class="features.length < 1 ? 'border-none' : ''" class="w-full min-w-[200px]">
                                <ComboboxGroup :class="features.length < 1 ? 'p-0' : ''">
                                    <ComboboxItem
                                        v-for="(feature, index) in features"
                                        :key="feature.value"
                                        :value="feature"
                                        @select.prevent="() => {
                                            const exists = form.features.some(u => u.value === feature.value)
                                            if (!exists) {
                                                form.features.push(feature)
                                            }
                                            comboboxFeatureSearchTerm = ''
                                            open = false
                                        }"
                                    >
                                        {{ feature.name }}
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>

                        <div v-if="form.features.length > 0" class="mt-4 pt-4 border-t text-sm">
                            <div>
                                <div class="flex items-center border-none p-0 gap-2">
                                    <draggable
                                        :list="form.features"
                                        item-key="id"
                                        class="flex items-center flex-wrap border-none p-0 gap-2"
                                        ghost-class="opacity-35"
                                        @start="dragging = true"
                                        @end="dragging = false"
                                    >
                                        <template #item="{ element }">
                                            <div class="h-auto bg-muted rounded">
                                                <div class="flex items-center justify-start gap-2 ps-2 pe-3 py-2">
                                                    <GripVertical class="h-3 w-3 opacity-30 hover:opacity-100 hover:cursor-move drag-handle" />
                                                    <div v-if="element.icon" class="feature-icon-dashboard" v-html="element.icon"></div>
                                                    <CheckIcon v-else class="h-4 w-4 stroke-2" />
                                                    <span class="me-4">
                                                        {{ element.name }}
                                                    </span>
                                                    <Button 
                                                        type="button"
                                                        variant="outline" 
                                                        size="icon" 
                                                        class="h-auto w-auto mt-0.5 bg-transparent shadow-none border-none opacity-50 hover:opacity-100"
                                                        @click="removeFeature(element)"
                                                    >
                                                        <X class="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </template>
                                    </draggable>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto text-right">
                        <Button :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
