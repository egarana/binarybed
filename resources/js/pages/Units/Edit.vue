<script setup lang="ts">
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { debounce } from 'lodash-es';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Check, CheckIcon, ChevronsUpDown, GripVertical, Loader2, Pencil, PlusCircle, Search, Trash2, X } from "lucide-vue-next";
import { cn } from "@/lib/utils";
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Separator } from '@/components/ui/separator';
import Table from '@/components/ui/table/Table.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger
} from "@/components/ui/combobox";
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue';
import TooltipProvider from '@/components/ui/tooltip/TooltipProvider.vue';
import Tooltip from '@/components/ui/tooltip/Tooltip.vue';
import TooltipTrigger from '@/components/ui/tooltip/TooltipTrigger.vue';
import TooltipContent from '@/components/ui/tooltip/TooltipContent.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogClose from '@/components/ui/dialog/DialogClose.vue';
import NumberField from '@/components/ui/number-field/NumberField.vue';
import NumberFieldContent from '@/components/ui/number-field/NumberFieldContent.vue';
import NumberFieldInput from '@/components/ui/number-field/NumberFieldInput.vue';
import Tabs from '@/components/ui/tabs/Tabs.vue';
import TabsList from '@/components/ui/tabs/TabsList.vue';
import TabsTrigger from '@/components/ui/tabs/TabsTrigger.vue';
import TabsContent from '@/components/ui/tabs/TabsContent.vue';
import draggable from 'vuedraggable'

// -------------------------
// Setup
// -------------------------
dayjs.extend(relativeTime);

interface PropertyOption {
    value: string;
    label: string;
}

interface FeatureOption {
    value: string;
    name: string;
    icon: string;
}

const page = usePage();
const unit = page.props.unit as {
    id: number;
    name: string;
    property?: PropertyOption | null;
    features?: FeatureOption | null;
};

const properties = ref<PropertyOption[]>([]);
const features = ref<FeatureOption[]>([])
const rates = ref(page.props.rates);

const comboboxSearchTerm = ref('');
const comboboxFeatureSearchTerm = ref('');
const sort = ref(page.props.sort || '');
const sortField = ref('name'); // default field

const form = useForm({
    name: unit.name,
    property: unit.property ?? null,
    features: unit.features ?? null,
});
const selectedProperty = ref(form.property ?? undefined);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Units', href: '/units' },
    { title: 'Edit Unit', href: route('units.edit', unit.id) },
];

const deleteDialogStates = reactive<Record<number, boolean>>({});
const editDialogStates = reactive<Record<number, boolean>>({});

const deleteRateForm = useForm({ _method: 'DELETE' });
const rateForm = useForm({ 
    name: '', 
    price: 0,
    unit_id: unit.id,
});

const selectedTab = ref('rates')
const open = ref(false)

// -------------------------
// Functions
// -------------------------
const onTabChange = (value: string) => {
    selectedTab.value = value
    console.log('Selected tab:', value)
}

const submit = () => {
    form.put(route('units.update', unit.id), {
        preserveScroll: false,
        onSuccess: () => {
            toast('Unit updated', {
                description: 'The unit has been updated successfully',
                action: { label: 'Close' },
            });
        },
        onError: (errors) => {
            toast('Error updating unit', {
                // description: Object.values(errors)[0] || 'Something went wrong, please try again',
                description: 'Something went wrong, please try again',
                action: { label: 'Close' },
            });
        },
    });
};

const handleKeydown = (e: KeyboardEvent) => {
    const isMac = navigator.platform.toUpperCase().includes('MAC');
    const isSaveShortcut = (isMac && e.metaKey && e.key === 's') || (!isMac && e.ctrlKey && e.key === 's');

    if (isSaveShortcut) {
        e.preventDefault();
        submit();
    }
};

const fetchComboboxData = debounce(() => {
    router.get(route('units.edit', unit.id), { search: comboboxSearchTerm.value }, {
        preserveScroll: true,
        preserveState: true,
        only: ['properties'],
        onSuccess: () => {
            const newProps = usePage().props.properties as PropertyOption[] ?? [];
            properties.value = newProps;
        }
    });
}, 300);

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'IDR',
        currencyDisplay: 'narrowSymbol',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
};

const handleSort = (field: string) => {
    if (sortField.value === field) {
        sort.value = sort.value === field ? `-${field}` : field;
    } else {
        sortField.value = field;
        sort.value = field;
    }
    fetchRatesData()
};

const fetchRatesData = () => {
    router.get(route('units.edit', unit.id), {
        sort: sort.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['rates'],
        onSuccess: () => {
            rates.value = usePage().props.rates;
        }
    });
};

const deleteItem = (e: Event, id: number) => {
    e.preventDefault();

    deleteRateForm.delete(route('rates.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal(id);
            toast('Rate deleted', {
                description: 'The rate has been deleted successfully',
                action: { label: 'Close' },
            });
        },
        onError: (errors) => {
            if (errors.authorization) {
                toast('Forbidden', {
                    description: errors.authorization,
                    action: { label: 'Close' },
                });
            }
        },
        onFinish: () => {
            fetchRatesData();
            deleteRateForm.reset();
        },
    });
};

const closeDeleteModal = (itemId: number) => {
    deleteRateForm.clearErrors();
    deleteRateForm.reset();
    deleteDialogStates[itemId] = false;
};

const fetchRateData = (id) => {
    router.get(route('units.edit', unit.id), { rateId: id }, {
        preserveScroll: true,
        preserveState: true,
        only: ['rate'],
        onSuccess: () => {
            const fetchedRate = usePage().props.rate;
            if (fetchedRate) {
                rateForm.name = fetchedRate.name || '';
                rateForm.price = fetchedRate.price || 0;
            }
        }
    });
};

const closeEditModal = (itemId: number) => {
    rateForm.clearErrors();
    editDialogStates[itemId] = false;
};

const submitCreateRate = () => {
    rateForm.post(route('rates.store'), {
        preserveScroll: false,
        onSuccess: () => {
            fetchRatesData();
            closeEditModal(999);
            toast('Rate created', {
                description: 'The rate has been created successfully',
                action: { label: 'Close' },
            });
        },
        onError: () => {
            toast('Error creating rate', {
                description: 'Something went wrong, please try again',
                action: { label: 'Close' },
            });
        },
    });
};

const submitEditRate = (id) => {
    rateForm.put(route('rates.update', id), {
        preserveScroll: false,
        onSuccess: () => {
            fetchRatesData();
            closeEditModal(id);
            toast('Rate updated', {
                description: 'The rate has been updated successfully',
                action: { label: 'Close' },
            });
        },
        onError: () => {
            toast('Error updating rate', {
                description: 'Something went wrong, please try again',
                action: { label: 'Close' },
            });
        },
    });
};

const fetchFeatureOptions = debounce(() => {
    router.get(route('units.edit', unit.id), {
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

const removeFeature = (feature: FeatureOption) => {
    form.features = form.features.filter(f => f.value !== feature.value)
}

// -------------------------
// Watchers
// -------------------------
watch(comboboxSearchTerm, (term) => {
    if (!term) {
        properties.value = [];
        return;
    }
    fetchComboboxData();
});

watch(selectedProperty, (newVal) => {
    form.property = newVal ?? null;
});

watch(comboboxFeatureSearchTerm, (term) => {
    if (!term) {
        features.value = []
        return
    }

    fetchFeatureOptions()
})

// -------------------------
// Lifecycle
// -------------------------
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head title="Edit Unit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <pre class="text-xs">{{ form }}</pre> -->
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex flex-col space-y-6 min-h-full">
                <HeadingSmall title="Edit unit" description="Add a new unit to include it in your management system" />

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
    
                                <ComboboxList align="start"  class="w-full min-w-[200px]">
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
                    
                    <Tabs v-model="selectedTab" class="grid gap-4">
                        <div class="flex items-center justify-between">
                            <TabsList class="grid w-[250px] grid-cols-2">
                                <TabsTrigger value="rates" class="text-muted-foreground hover:cursor-pointer data-[state=active]:text-foreground dark:data-[state=active]:bg-background">
                                    Rates
                                </TabsTrigger>
                                <TabsTrigger value="features" class="text-muted-foreground hover:cursor-pointer data-[state=active]:text-foreground dark:data-[state=active]:bg-background">
                                    Features
                                </TabsTrigger>
                            </TabsList>

                            <Dialog 
                                v-if="selectedTab === 'rates'"
                                v-model:open="editDialogStates[999]"
                                @update:open="() => rateForm.reset('name', 'price')"
                            >
                                <DialogTrigger as-child>
                                    <Button variant="outline">
                                        <PlusCircle /> Add Rate
                                    </Button>
                                </DialogTrigger>

                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Add rate</DialogTitle>
                                        <DialogDescription>
                                            Make changes to your rate here. Click save when you're done.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <div class="space-y-4">
                                        <div class="grid gap-2">
                                            <Label for="rateName">Name</Label>
                                            <Input 
                                                id="rateName" 
                                                class="mt-1 block w-full disabled:bg-muted disabled:text-muted-foreground" 
                                                v-model="rateForm.name" 
                                                autocomplete="rateName" 
                                                placeholder="e.g. Non-refundable, Early Bird" 
                                                :disabled="rateForm.name === 'Standard Rate'"
                                            />
                                            <InputError :message="rateForm.errors.name" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="ratePrice">Price</Label>
                                            <NumberField 
                                                id="ratePrice" 
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
                                                v-model="rateForm.price"
                                            >
                                                <NumberFieldContent>
                                                    <NumberFieldInput class="text-start px-3 text-sm" />
                                                </NumberFieldContent>
                                            </NumberField>
                                            <InputError :message="rateForm.errors.price" />
                                        </div>
                                    </div>

                                    <DialogFooter class="mt-4">
                                        <DialogClose as-child>
                                            <Button 
                                                :disabled="rateForm.processing"
                                                variant="outline" 
                                                @click="closeEditModal"
                                            >
                                                Cancel
                                            </Button>
                                        </DialogClose>

                                        <Button
                                            :disabled="rateForm.processing"
                                            type="button"
                                            @click="submitCreateRate()"
                                        >
                                            Save
                                            <Loader2 v-if="rateForm.processing" class="animate-spin"/>
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <TabsContent value="rates">
                            <div class="grid gap-2">
                                <div class="overflow-hidden rounded-lg border">
                                    <div class="relative w-full overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow class="bg-muted">
                                                    <TableHead>
                                                        <Button type="button" variant="ghost" size="sm" @click="handleSort('name')">
                                                            Name
                                                            <ChevronsUpDown class="!w-3 !h-3" />
                                                        </Button>
                                                    </TableHead>
                                                    <TableHead class="w-[140px] text-right">
                                                        <Button type="button" variant="ghost" size="sm" @click="handleSort('price')">
                                                            Price
                                                            <ChevronsUpDown class="!w-3 !h-3" />
                                                        </Button>
                                                    </TableHead>
                                                    <TableHead class="w-[140px]">
                                                        <Button type="button" variant="ghost" size="sm" @click="handleSort('created_at')">
                                                            Created At
                                                            <ChevronsUpDown class="!w-3 !h-3" />
                                                        </Button>
                                                    </TableHead>
                                                    <TableHead class="w-[140px]">
                                                        <Button type="button" variant="ghost" size="sm" @click="handleSort('updated_at')">
                                                            Updated At
                                                            <ChevronsUpDown class="!w-3 !h-3" />
                                                        </Button>
                                                    </TableHead>
                                                    <TableHead class="w-[88px]"></TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="rate in rates" :key="rate.id" v-if="rates.length">
                                                    <TableCell class="ps-5 py-4">{{ rate.name }}</TableCell>
                                                    <TableCell class="pe-6 font-medium text-right">{{ formatNumber(rate.price) }}</TableCell>
                                                    <TableCell class="ps-5">{{ dayjs(rate.created_at).fromNow() }}</TableCell>
                                                    <TableCell class="ps-5">{{ dayjs(rate.updated_at).fromNow() }}</TableCell>
                                                    <TableCell class="text-right">

                                                        <Dialog v-model:open="editDialogStates[rate.id]">
                                                            <TooltipProvider>
                                                                <Tooltip>
                                                                    <TooltipTrigger type="button">
                                                                        <DialogTrigger as-child>
                                                                            <Button type="button" variant="ghost" size="icon" @click="() => fetchRateData(rate.id)">
                                                                                <Pencil class="w-4 h-4 text-muted-foreground" />
                                                                            </Button>
                                                                        </DialogTrigger>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>
                                                                        <p>Edit Rate</p>
                                                                    </TooltipContent>
                                                                </Tooltip>
                                                            </TooltipProvider>
                                                            <DialogContent>
                                                                <DialogHeader>
                                                                    <DialogTitle>Edit rate</DialogTitle>
                                                                    <DialogDescription>
                                                                        Make changes to your rate here. Click save when you're done.
                                                                    </DialogDescription>
                                                                </DialogHeader>

                                                                <div class="space-y-4">
                                                                    <div class="grid gap-2">
                                                                        <Label for="rateName">Name</Label>
                                                                        <Input 
                                                                            id="rateName" 
                                                                            class="mt-1 block w-full disabled:bg-muted disabled:text-muted-foregroundu" 
                                                                            v-model="rateForm.name" 
                                                                            autocomplete="rateName" 
                                                                            placeholder="e.g. Non-refundable, Early Bird" 
                                                                            :disabled="rateForm.name === 'Standard Rate'"
                                                                        />
                                                                        <InputError :message="rateForm.errors.name" />
                                                                    </div>

                                                                    <div class="grid gap-2">
                                                                        <Label for="ratePrice">Price</Label>
                                                                        <NumberField 
                                                                            id="ratePrice" 
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
                                                                            v-model="rateForm.price"
                                                                        >
                                                                            <NumberFieldContent>
                                                                                <NumberFieldInput class="text-start px-3 text-sm" />
                                                                            </NumberFieldContent>
                                                                        </NumberField>
                                                                        <InputError :message="rateForm.errors.price" />
                                                                    </div>
                                                                </div>

                                                                <DialogFooter class="mt-4">
                                                                    <DialogClose as-child>
                                                                        <Button 
                                                                            :disabled="rateForm.processing"
                                                                            variant="outline" 
                                                                            @click="closeEditModal"
                                                                        >
                                                                            Cancel
                                                                        </Button>
                                                                    </DialogClose>

                                                                    <Button
                                                                        :disabled="rateForm.processing"
                                                                        type="button"
                                                                        @click="submitEditRate(rate.id)"
                                                                    >
                                                                        Save
                                                                        <Loader2 v-if="rateForm.processing" class="animate-spin"/>
                                                                    </Button>
                                                                </DialogFooter>
                                                            </DialogContent>
                                                        </Dialog>

                                                        <Dialog
                                                            v-if="rate.name !== 'Standard Rate'"
                                                            v-model:open="deleteDialogStates[rate.id]"
                                                        >
                                                            <TooltipProvider>
                                                                <Tooltip>
                                                                    <TooltipTrigger type="button">
                                                                        <DialogTrigger as-child>
                                                                            <Button type="button" variant="ghost" size="icon">
                                                                                <Trash2 class="w-4 h-4 text-muted-foreground" />
                                                                            </Button>
                                                                        </DialogTrigger>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>
                                                                        <p>Delete Rate</p>
                                                                    </TooltipContent>
                                                                </Tooltip>
                                                            </TooltipProvider>
                                                            <DialogContent>
                                                                <div class="space-y-6">
                                                                    <DialogHeader>
                                                                        <DialogTitle>Are you sure you want to delete this rate?</DialogTitle>
                                                                        <DialogDescription>
                                                                            If you delete this rate, everything connected to it will also be permanently removed. This action cannot be undone.
                                                                        </DialogDescription>
                                                                    </DialogHeader>

                                                                    <DialogFooter>
                                                                        <DialogClose as-child>
                                                                            <Button 
                                                                                type="button" 
                                                                                variant="outline" 
                                                                                @click="closeDeleteModal"
                                                                                :disabled="deleteRateForm.processing"
                                                                            >
                                                                                Cancel
                                                                            </Button>
                                                                        </DialogClose>

                                                                        <Button 
                                                                            type="button" 
                                                                            @click.prevent="(e) => deleteItem(e, rate.id)"
                                                                            variant="destructive" 
                                                                            :disabled="deleteRateForm.processing"
                                                                        >
                                                                            Delete Rate
                                                                        </Button>
                                                                    </DialogFooter>
                                                                </div>
                                                            </DialogContent>
                                                        </Dialog>
                                                        <div v-else class="inline-block w-9 bg-red-100"></div>
                                                    </TableCell>
                                                </TableRow>
                                                <TableRow v-else>
                                                    <TableCell colspan="5" class="text-center text-muted-foreground py-4">No rates found.</TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>
                        <TabsContent value="features">
                            <Combobox v-model="form.features" v-model:open="open" class="combobox-tags-input-wrapper">
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
                        </TabsContent>
                    </Tabs>
                    

                    <div class="mt-auto text-right">
                        <Button :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
