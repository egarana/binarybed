<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Select from '@/components/ui/select/Select.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import Table from '@/components/ui/table/Table.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import Tooltip from '@/components/ui/tooltip/Tooltip.vue';
import TooltipContent from '@/components/ui/tooltip/TooltipContent.vue';
import TooltipProvider from '@/components/ui/tooltip/TooltipProvider.vue';
import TooltipTrigger from '@/components/ui/tooltip/TooltipTrigger.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ChevronsUpDown, Pencil, PlusCircle, Trash2, X } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogClose from '@/components/ui/dialog/DialogClose.vue';
import { toast } from 'vue-sonner';
import Badge from '@/components/ui/badge/Badge.vue';

const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

dayjs.extend(relativeTime);

const page = usePage()
const properties = ref(page.props.properties)
const perPage = ref(String(page.props.perPage || 20))
const search = ref(page.props.search || '')
const sort = ref(page.props.sort || '') // current sort
const sortField = ref('name') // default field
const isLoading = ref(true)

const dialogStates = reactive<Record<number, boolean>>({});

const form = useForm({
    _method: 'DELETE',
});

const deleteItem = (e: Event, id: number) => {
    e.preventDefault();

    form.delete(route('properties.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal(id);

            toast('Property deleted', {
                description: 'The property has been deleted successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: (errors) => {
            if (errors.authorization) {
                toast('Forbidden', {
                    description: errors.authorization,
                    action: {
                        label: 'Close',
                    },
                });
            }
        },
        onFinish: () => {
            fetchData()
            form.reset()
        },
    });
};

const closeModal = (itemId: number) => {
    form.clearErrors();
    form.reset();
    dialogStates[itemId] = false;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Properties',
        href: '/properties',
    },
];

const fetchData = () => {
    isLoading.value = true

    router.get('/properties', {
        search: search.value,
        perPage: perPage.value,
        sort: sort.value,
        page: properties.value.current_page,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['properties'],
        onSuccess: () => {
            properties.value = usePage().props.properties
        },
        onFinish: () => {
            isLoading.value = false // stop loading
        }
    })
}

onMounted(() => {
    isLoading.value = false
    properties.value.data.forEach(property => {
        if (!(property.id in dialogStates)) {
            dialogStates[property.id] = false;
        }
    });
})

const handleSort = (field: string) => {
    if (sortField.value === field) {
        sort.value = sort.value === field ? `-${field}` : field
    } else {
        sortField.value = field
        sort.value = field
    }
    fetchData()
}

watch([search, perPage], () => {
    properties.value.current_page = 1;
    fetchData();
})

const resetFilters = () => {
    search.value = '';
};

const isFirstPage = computed(() => properties.value.current_page === 1)
const isLastPage = computed(() => properties.value.current_page === properties.value.last_page)
</script>

<template>
    <Head title="Properties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
                <Input
                    v-model="search"
                    id="search"
                    placeholder="Search properties..."
                    class="text-sm md:max-w-[250px]"
                />
                <Button
                    v-if="search !== ''"
                    variant="ghost"
                    @click="resetFilters"
                    :disabled="search === ''"
                    class="w-full border md:border-0 md:w-auto"
                >
                    Reset
                    <X/>
                </Button>
                <Link :href="'/properties/create'" class="ms-auto">
                    <Button class="w-full md:w-auto">
                        <PlusCircle /> Add Property
                    </Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg border">
                <div class="relative w-full overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted">
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="handleSort('name')">
                                        Name
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[250px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('domain')">
                                        Domain
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[90px] text-center">
                                    <Button variant="ghost" size="sm" @click="handleSort('units_count')">
                                        Units
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[90px] text-center">
                                    <Button variant="ghost" size="sm" @click="handleSort('users_count')">
                                        Users
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[140px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('created_at')">
                                        Created At
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[140px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('updated_at')">
                                        Updated At
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[88px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="property in properties.data" :key="property.id" v-if="properties.data.length">
                                <TableCell
                                    :class="(!permissions.includes('delete-properties') && !permissions.includes('edit-properties')) ? '!py-4' : ''"
                                    class="ps-5"
                                >
                                    {{ property.name }}
                                </TableCell>
                                <TableCell class="ps-5">{{ property.domain }}</TableCell>
                                <TableCell class="text-center pe-5">{{ property.units_count }}</TableCell>
                                <TableCell class="text-center pe-5">{{ property.users_count }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(property.created_at).fromNow() }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(property.updated_at).fromNow() }}</TableCell>
                                <TableCell class="text-right">
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Link :href="route('properties.edit', property.id)" class="ms-auto">
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil class="w-4 h-4 text-muted-foreground" />
                                                    </Button>
                                                </Link>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Edit Property</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <Dialog v-model:open="dialogStates[property.id]">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger>
                                                    <DialogTrigger as-child>
                                                        <Button variant="ghost" size="icon">
                                                            <Trash2 class="w-4 h-4 text-muted-foreground" />
                                                        </Button>
                                                    </DialogTrigger>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Delete Property</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        <DialogContent>
                                            <form class="space-y-6" @submit="(e) => deleteItem(e, property.id)">
                                                <DialogHeader>
                                                    <DialogTitle>Are you sure you want to delete this property?</DialogTitle>
                                                    <DialogDescription>
                                                        If you delete this property, everything connected to it will also be permanently removed. This action cannot be undone.
                                                    </DialogDescription>
                                                </DialogHeader>

                                                <DialogFooter>
                                                    <DialogClose as-child>
                                                        <Button variant="outline" @click="closeModal">
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>

                                                    <Button variant="destructive" :disabled="form.processing">
                                                        <button type="submit">Delete Property</button>
                                                    </Button>
                                                </DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>
                                </TableCell>
                            </TableRow>
                            <TableRow v-else>
                                <TableCell colspan="5" class="text-center text-muted-foreground py-4">No properties found.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <div v-if="properties.data.length" class="px-2 mt-auto pt-1 flex flex-wrap items-center md:justify-end md:gap-4 lg:gap-10">
                <div class="basis-full text-sm text-muted-foreground shrink-0 mb-4 md:mb-0 md:basis-auto md:me-auto md:order-1">
                    Showing {{ properties.from }} to {{ properties.to }} of {{ properties.total }} records.
                </div>
                <div class="basis-1/3 text-sm font-medium shrink-0 md:basis-auto md:order-3">
                    Page {{ properties.current_page }} of {{ properties.last_page }}
                </div>
                <div class="basis-2/3 flex items-center justify-end gap-2 md:basis-auto md:order-4">
                    <!-- Active button -->
                    <Link v-if="!isFirstPage" :href="properties.first_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronsLeft class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronsLeft class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                     <!-- Active button -->
                    <Link v-if="properties.prev_page_url" :href="properties.prev_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronLeft class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronLeft class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                    <!-- Active button -->
                    <Link v-if="properties.next_page_url" :href="properties.next_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronRight class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                    <!-- Active button -->
                    <Link v-if="!isLastPage" :href="properties.last_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronsRight class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronsRight class="w-4 h-4 text-muted-foreground/50" />
                    </Button>
                </div>
                <div class="basis-full text-sm font-medium flex items-center justify-between gap-2 shrink-0 mt-4 md:mt-0 md:basis-auto md:order-2">
                    <span>Rows per page</span>
                    <Select v-model="perPage">
                        <SelectTrigger class="w-[70px]">
                            <SelectValue placeholder="Select..."/>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="20">20</SelectItem>
                            <SelectItem value="30">30</SelectItem>
                            <SelectItem value="40">40</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
