<script setup lang="ts">
// Vue & Inertia
import { computed, onMounted, reactive, ref, watch } from "vue"
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3"

// Utilities
import { cn } from "@/lib/utils"
import dayjs from "dayjs"
import relativeTime from "dayjs/plugin/relativeTime"
import { toast } from "vue-sonner"
import { parseDate, getLocalTimeZone, type DateValue } from "@internationalized/date"

import {
    CalendarDays,
    Check,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    ChevronsUpDown,
    Pencil,
    PlusCircle,
    Trash2,
    X,
} from "lucide-vue-next"

import AppLayout from "@/layouts/AppLayout.vue"
import { type BreadcrumbItem } from "@/types"

import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import Badge from "@/components/ui/badge/Badge.vue"

import Table from "@/components/ui/table/Table.vue"
import TableBody from "@/components/ui/table/TableBody.vue"
import TableCell from "@/components/ui/table/TableCell.vue"
import TableHead from "@/components/ui/table/TableHead.vue"
import TableHeader from "@/components/ui/table/TableHeader.vue"
import TableRow from "@/components/ui/table/TableRow.vue"

import Input from "@/components/ui/input/Input.vue"
import Select from "@/components/ui/select/Select.vue"
import SelectContent from "@/components/ui/select/SelectContent.vue"
import SelectItem from "@/components/ui/select/SelectItem.vue"
import SelectTrigger from "@/components/ui/select/SelectTrigger.vue"
import SelectValue from "@/components/ui/select/SelectValue.vue"

import Tooltip from "@/components/ui/tooltip/Tooltip.vue"
import TooltipContent from "@/components/ui/tooltip/TooltipContent.vue"
import TooltipProvider from "@/components/ui/tooltip/TooltipProvider.vue"
import TooltipTrigger from "@/components/ui/tooltip/TooltipTrigger.vue"

import Dialog from "@/components/ui/dialog/Dialog.vue"
import DialogTrigger from "@/components/ui/dialog/DialogTrigger.vue"
import DialogContent from "@/components/ui/dialog/DialogContent.vue"
import DialogHeader from "@/components/ui/dialog/DialogHeader.vue"
import DialogTitle from "@/components/ui/dialog/DialogTitle.vue"
import DialogDescription from "@/components/ui/dialog/DialogDescription.vue"
import DialogFooter from "@/components/ui/dialog/DialogFooter.vue"
import DialogClose from "@/components/ui/dialog/DialogClose.vue"

import Combobox from "@/components/ui/combobox/Combobox.vue"
import ComboboxAnchor from "@/components/ui/combobox/ComboboxAnchor.vue"
import ComboboxTrigger from "@/components/ui/combobox/ComboboxTrigger.vue"
import ComboboxList from "@/components/ui/combobox/ComboboxList.vue"
import ComboboxGroup from "@/components/ui/combobox/ComboboxGroup.vue"
import ComboboxItem from "@/components/ui/combobox/ComboboxItem.vue"
import ComboboxItemIndicator from "@/components/ui/combobox/ComboboxItemIndicator.vue"

const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

dayjs.extend(relativeTime);

interface DateOfOption {
  value: string
  label: string
}

const dateOfOptions = [
    { value: "check_in", label: "Check-in" },
    { value: "check_out", label: "Check-out" },
    { value: "booked_on", label: "Reservation" },
]

const page = usePage()
const reservations = ref(page.props.reservations)
const perPage = ref(String(page.props.perPage || 20))
const search = ref(page.props.search || '')
// const sort = ref(page.props.sort || '') // current sort
const sort = ref(page.props.sort || "")
const sortField = ref('id') // default field
const isLoading = ref(true)
const dateOf = ref<DateOfOption>(dateOfOptions[0])
const fromDate = ref<DateValue | undefined>(
    page.props.fromDate ? parseDate(page.props.fromDate) : undefined
)

const toDate = ref<DateValue | undefined>(
    page.props.toDate ? parseDate(page.props.toDate) : undefined
)

const openFrom = ref(false)
const openTo = ref(false)

const dialogStates = reactive<Record<number, boolean>>({});

const form = useForm({
    _method: 'DELETE',
});

const deleteItem = (e: Event, id: number) => {
    e.preventDefault();

    form.delete(route('reservations.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal(id);

            toast('Reservation deleted', {
                description: 'The reservation has been deleted successfully',
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
        title: 'Reservations',
        href: '/reservations',
    },
];

const formatDate = (date: DateValue | undefined) => {
    return date ? dayjs(date.toDate(getLocalTimeZone())).format("YYYY-MM-DD") : null
}

const fetchData = () => {
    isLoading.value = true

    router.get('/reservations', {
        search: search.value,
        dateOf: dateOf.value.value,
        from: formatDate(fromDate.value),
        until: formatDate(toDate.value),
        perPage: perPage.value,
        sort: sort.value,
        page: reservations.value.current_page,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['reservations'],
        onSuccess: () => {
            reservations.value = usePage().props.reservations
        },
        onFinish: () => {
            isLoading.value = false // stop loading
        }
    })
}

onMounted(() => {
    isLoading.value = false
    reservations.value.data.forEach(reservation => {
        if (!(reservation.id in dialogStates)) {
            dialogStates[reservation.id] = false;
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

watch([search, perPage, dateOf, fromDate, toDate], () => {
    reservations.value.current_page = 1;
    fetchData();
})

// Handle From Date selection
const handleFromSelect = (date: DateValue | undefined) => {
    if (!date) return
    fromDate.value = date
    openFrom.value = false

    if (!toDate.value) {
        // If toDate not set yet → set toDate = fromDate + 1 day
        toDate.value = date.add({ days: 1 })
    } else if (date.compare(toDate.value) > 0) {
        // If fromDate > toDate → shift toDate = fromDate + 1 day
        toDate.value = date.add({ days: 1 })
    }
}

// Handle To Date selection
const handleToSelect = (date: DateValue | undefined) => {
    if (!date) return
    toDate.value = date
    openTo.value = false

    if (!fromDate.value) {
        // If fromDate not set yet → set fromDate = toDate - 1 day
        fromDate.value = date.subtract({ days: 1 })
    } else if (date.compare(fromDate.value) < 0) {
        // If toDate < fromDate → shift fromDate = toDate - 1 day
        fromDate.value = date.subtract({ days: 1 })
    }
}

const showReset = computed(() => {
    return (
        search.value !== "" ||
        fromDate.value !== undefined ||
        toDate.value !== undefined ||
        sort.value !== ""
    )
})

const resetFilters = () => {
    search.value = ""
    dateOf.value = dateOfOptions[0]
    fromDate.value = undefined
    toDate.value = undefined
    sort.value = "" // match default
    fetchData()
}

const isFirstPage = computed(() => reservations.value.current_page === 1)
const isLastPage = computed(() => reservations.value.current_page === reservations.value.last_page)

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'IDR',
        currencyDisplay: 'narrowSymbol', // "Rp" instead of "IDR"
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
}
</script>

<template>
    <Head title="Reservations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
                <Input
                    v-model="search"
                    id="search"
                    placeholder="Search reservations..."
                    class="text-sm md:max-w-[250px]"
                />
                
                <Combobox v-model="dateOf">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
                                <CalendarDays class="stroke-[1.8]"/>
                                <span>Date of</span>
                                <div v-if="dateOf" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
                                    <Badge variant="secondary" class="font-normal rounded-sm px-1.5">
                                        {{ dateOf.label }}
                                    </Badge>
                                </div>
                            </Button>
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList align="start" class="w-full min-w-[200px]">
                        <ComboboxGroup>
                            <ComboboxItem
                                v-for="(item, index) in dateOfOptions"
                                :key="index"
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
                
                <Popover v-model:open="openFrom">
                    <PopoverTrigger as-child>
                        <Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
                            <PlusCircle/>
                            <span>From</span>
                            <div v-if="fromDate" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
                                <Badge variant="secondary" class="font-normal rounded-sm px-1.5">
                                    {{ dayjs(fromDate.toDate(getLocalTimeZone())).format("MMM DD, YYYY") }}
                                </Badge>
                            </div>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent align="start" class="w-auto p-0">
                        <Calendar
                            v-model="fromDate"
                            initial-focus
                            @update:model-value="handleFromSelect"
                        />
                    </PopoverContent>
                </Popover>

                <!-- To Date -->
                <Popover v-model:open="openTo">
                    <PopoverTrigger as-child>
                        <Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
                            <PlusCircle/>
                            <span>Until</span>
                            <div v-if="toDate" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
                                <Badge variant="secondary" class="font-normal rounded-sm px-1.5">
                                    {{ dayjs(toDate.toDate(getLocalTimeZone())).format("MMM DD, YYYY") }}
                                </Badge>
                            </div>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent align="start" class="w-auto p-0">
                        <Calendar
                            v-model="toDate"
                            initial-focus
                            @update:model-value="handleToSelect"
                        />
                    </PopoverContent>
                </Popover>
                
                <Button
                    v-if="showReset"
                    variant="ghost"
                    @click="resetFilters"
                    class="w-full border md:border-0 md:w-auto"
                >
                    Reset
                    <X/>
                </Button>
                <Link :href="'/reservations/create'" class="ms-auto">
                    <Button class="w-full md:w-auto">
                        <PlusCircle /> Create Reservation
                    </Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg border">
                <div class="relative w-full overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted">
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="handleSort('first_name')">
                                        Name
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[120px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('check_in')">
                                        Check-in
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[120px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('check_out')">
                                        Check-out
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="handleSort('unit')">
                                        Unit
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[120px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('booked_on')">
                                        Booked On
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[150px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('rate')">
                                        Rate
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <!-- <TableHead class="w-[250px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('property')">
                                        Property
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[140px] text-right">
                                    <Button variant="ghost" size="sm" @click="handleSort('standard_rate_price')">
                                        Standard Rate
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[90px] text-center">
                                    <Button variant="ghost" size="sm" @click="handleSort('rates_count')">
                                        Rates
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead> -->
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
                            <TableRow v-for="reservation in reservations.data" :key="reservation.id" v-if="reservations.data.length">
                                <TableCell class="ps-5">{{ reservation.first_name }} {{ reservation.last_name }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(reservation.check_in).format('DD MMM YYYY') }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(reservation.check_out).format('DD MMM YYYY') }}</TableCell>
                                <TableCell class="ps-5">{{reservation.unit.name }} - {{reservation.unit.property.name }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(reservation.booked_on).format('DD MMM YYYY') }}</TableCell>
                                <TableCell class="ps-5">{{reservation.rate.name }}</TableCell>
                                <!-- <TableCell
                                    :class="(!permissions.includes('delete-reservations') && !permissions.includes('edit-reservations')) ? '!py-4' : ''"
                                    class="ps-5"
                                >
                                    {{ reservation.name }}
                                </TableCell> -->
                                <!-- <TableCell class="ps-5">
                                    <span v-if="reservation.property">{{ reservation.property.name }}</span>
                                    <span v-else class="text-muted-foreground italic">No property assigned</span>
                                </TableCell>
                                <TableCell class="pe-6 font-medium text-right">{{ formatNumber(reservation.standard_rate.price) }}</TableCell>
                                <TableCell class="text-center pe-5">{{ reservation.rates_count }}</TableCell> -->
                                <TableCell class="ps-5">{{ dayjs(reservation.created_at).fromNow() }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(reservation.updated_at).fromNow() }}</TableCell>
                                <TableCell class="text-right">
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Link :href="route('reservations.edit', reservation.id)" class="ms-auto">
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil class="w-4 h-4 text-muted-foreground" />
                                                    </Button>
                                                </Link>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Edit Reservation</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <Dialog v-model:open="dialogStates[reservation.id]">
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
                                                    <p>Delete Reservation</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        <DialogContent>
                                            <form class="space-y-6" @submit="(e) => deleteItem(e, reservation.id)">
                                                <DialogHeader>
                                                    <DialogTitle>Are you sure you want to delete this reservation?</DialogTitle>
                                                    <DialogDescription>
                                                        If you delete this reservation, everything connected to it will also be permanently removed. This action cannot be undone.
                                                    </DialogDescription>
                                                </DialogHeader>

                                                <DialogFooter>
                                                    <DialogClose as-child>
                                                        <Button variant="outline" @click="closeModal">
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>

                                                    <Button variant="destructive" :disabled="form.processing">
                                                        <button type="submit">Delete Reservation</button>
                                                    </Button>
                                                </DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>
                                </TableCell>
                            </TableRow>
                            <TableRow v-else>
                                <TableCell colspan="9" class="text-center text-muted-foreground py-4">No reservations found.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <div v-if="reservations.data.length" class="px-2 mt-auto pt-1 flex flex-wrap items-center md:justify-end md:gap-4 lg:gap-10">
                <div class="basis-full text-sm text-muted-foreground shrink-0 mb-4 md:mb-0 md:basis-auto md:me-auto md:order-1">
                    Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} records.
                </div>
                <div class="basis-1/3 text-sm font-medium shrink-0 md:basis-auto md:order-3">
                    Page {{ reservations.current_page }} of {{ reservations.last_page }}
                </div>
                <div class="basis-2/3 flex items-center justify-end gap-2 md:basis-auto md:order-4">
                    <!-- Active button -->
                    <Link v-if="!isFirstPage" :href="reservations.first_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronsLeft class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronsLeft class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                     <!-- Active button -->
                    <Link v-if="reservations.prev_page_url" :href="reservations.prev_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronLeft class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronLeft class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                    <!-- Active button -->
                    <Link v-if="reservations.next_page_url" :href="reservations.next_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronRight class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                    <!-- Active button -->
                    <Link v-if="!isLastPage" :href="reservations.last_page_url">
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
