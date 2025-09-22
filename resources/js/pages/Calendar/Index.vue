<script setup lang="ts">
/* ------------------------------ Imports ------------------------------ */
import AppLayout from '@/layouts/AppLayout.vue'

import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import Calendar from '@/components/ui/calendar/Calendar.vue'
import Combobox from '@/components/ui/combobox/Combobox.vue'
import ComboboxAnchor from '@/components/ui/combobox/ComboboxAnchor.vue'
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue'
import ComboboxInput from '@/components/ui/combobox/ComboboxInput.vue'
import ComboboxItem from '@/components/ui/combobox/ComboboxItem.vue'
import ComboboxItemIndicator from '@/components/ui/combobox/ComboboxItemIndicator.vue'
import ComboboxList from '@/components/ui/combobox/ComboboxList.vue'
import ComboboxTrigger from '@/components/ui/combobox/ComboboxTrigger.vue'

import Popover from '@/components/ui/popover/Popover.vue'
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue'
import PopoverContent from '@/components/ui/popover/PopoverContent.vue'

import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import DialogClose from '@/components/ui/dialog/DialogClose.vue'

import TooltipProvider from '@/components/ui/tooltip/TooltipProvider.vue'
import Tooltip from '@/components/ui/tooltip/Tooltip.vue'
import TooltipTrigger from '@/components/ui/tooltip/TooltipTrigger.vue'
import TooltipContent from '@/components/ui/tooltip/TooltipContent.vue'

import PlaceholderPattern from '@/components/PlaceholderPattern.vue'

import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { type BreadcrumbItem } from '@/types'

import {
    CalendarIcon,
    Check,
    ChevronsUpDown,
    LoaderCircle,
    NotebookPen,
    Search,
    Tag,
} from 'lucide-vue-next'

import dayjs from 'dayjs'
import { debounce, throttle } from 'lodash-es'
import {
    CalendarDate,
    parseDate,
    getLocalTimeZone,
    type DateValue,
    today,
} from '@internationalized/date'

import { computed, reactive, ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { toast } from 'vue-sonner'

/* ------------------------------ Types ------------------------------ */
interface RateOption {
    value: string | number
    label: string
    price?: number
}

interface UnitOption {
    value: string | number
    label: string
    name?: string
    qty?: number
    rates?: RateOption[]
}

interface ReservationSlot {
    id: number
    name: string
    btn_width: string
    is_first_day?: boolean
    nights_duration?: number
    booked_on?: string
    check_in?: string
    check_out?: string
}

interface CalendarDay {
	date: string
	day: string
	day_number: string
	month: string
	year: string | number
	is_weekend?: boolean
	is_month_end?: boolean
	is_open: boolean
	qty: number
	reservations_count?: number
	reservations?: ReservationSlot[]
	editing?: boolean
	localValue?: number
	saving?: boolean
	rates?: {
		[rateId: string]: {
			price: number
			editing?: boolean
			localValue?: number
			saving?: boolean
		}
	}
}

/* ------------------------------ Page / Props ------------------------------ */
const page = usePage()
const permissions = computed<string[]>(() => page.props.auth?.user?.permissions ?? [])

/* ------------------------------ State ------------------------------ */
const units = ref<UnitOption[]>([])
const selectedUnit = ref<UnitOption | undefined>(page.props.selectedUnit as UnitOption | undefined)
const comboboxUnitSearchTerm = ref('')

const openFrom = ref(false)
const openTo = ref(false)

const fromDate = ref<DateValue>(
    page.props.fromDate ? parseDate(page.props.fromDate) : today(getLocalTimeZone())
)
const toDate = ref<DateValue>(
    page.props.toDate ? parseDate(page.props.toDate) : today(getLocalTimeZone()).add({ days: 30 })
)

const selectedUnitId = computed<number | undefined>(() => {
    const v = selectedUnit.value?.value
    if (v === undefined || v === null) return undefined
    const n = Number(v)
    return Number.isNaN(n) ? undefined : n
})

const initialCalendarDays: CalendarDay[] = Array.isArray(page.props.calendarDays)
    ? page.props.calendarDays.map((d: any) => ({
        ...d,
        qty: d?.qty ?? 0,
        editing: false,
        localValue: d?.qty ?? 0,
        saving: false,
    }))
    : []

const calendarDays = ref<CalendarDay[]>(initialCalendarDays)
const dialogStates = reactive<Record<number, boolean>>({})
const inputRefs = new Map<string, HTMLInputElement>()

/* ------------------------------ UI Data ------------------------------ */
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Calendar', href: '/calendar' }]

/* ------------------------------ Fetchers ------------------------------ */
const fetchUnitsComboboxData = debounce(() => {
    router.get(
        route('calendar.index'),
        { search: comboboxUnitSearchTerm.value },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['units'],
            onSuccess: (pg) => {
                units.value = (pg.props.units as UnitOption[]) ?? []
            },
        }
    )
}, 300)

const isLoadingFetchCalendarDays = ref(false)

const fetchCalendarDays = debounce(() => {
    const unitId = selectedUnitId.value
    const from = formatDate(fromDate.value)
    const until = formatDate(toDate.value)
    if (!unitId || !from || !until) {
        isLoadingFetchCalendarDays.value = false
        return
    }

    isLoadingFetchCalendarDays.value = true
    router.get(
        route('calendar.index'),
        { unitId, from, until },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['calendarDays'],
            onSuccess: (pg) => {
                const serverDays = (pg.props.calendarDays as CalendarDay[]) ?? []
                calendarDays.value = serverDays.map((d) => ({
                    ...d,
                    qty: d?.qty ?? 0,
                    editing: false,
                    localValue: d?.qty ?? 0,
                    saving: false,
                }))
            },
            onFinish: () => {
                isLoadingFetchCalendarDays.value = false
            },
        }
    )
}, 300)

/* ------------------------------ Watches ------------------------------ */
watch(comboboxUnitSearchTerm, (term) => {
    if (!term) {
        units.value = []
        return
    }
    fetchUnitsComboboxData()
})

watch(selectedUnit, () => {
    isLoadingFetchCalendarDays.value = true
    fetchCalendarDays()
})

watch([fromDate, toDate], () => {
    fetchCalendarDays()
})

/* ------------------------------ Date Helpers ------------------------------ */
const formatDate = (date: DateValue | undefined): string | null =>
    date ? dayjs(date.toDate(getLocalTimeZone())).format('YYYY-MM-DD') : null

const disablePastDates = (date: DateValue) => {
    const cutoff = new CalendarDate(2025, 1, 1)
    return date.compare(cutoff) < 0
}

const disableUntilDates = (date: DateValue) => {
    const cutoffDate = new CalendarDate(2025, 1, 1)
    return date.compare(cutoffDate) <= 0 || (fromDate.value && date.compare(fromDate.value) <= 0)
}

const handleFromSelect = (date: DateValue | undefined) => {
    if (!date) return
    fromDate.value = date
    openFrom.value = false

    if (!toDate.value) {
        toDate.value = date.add({ days: 1 })
    } else if (date.compare(toDate.value) > 0) {
        toDate.value = date.add({ days: 1 })
    }
}

const handleToSelect = (date: DateValue | undefined) => {
    if (!date) return
    toDate.value = date
    openTo.value = false

    if (!fromDate.value) {
        fromDate.value = date.subtract({ days: 1 })
    } else if (date.compare(fromDate.value) < 0) {
        fromDate.value = date.subtract({ days: 1 })
    }
}

/* ------------------------------ Mutations ------------------------------ */
const loadingDatesStatus = ref<Set<string>>(new Set())
const loadingDatesQty = ref<Set<string>>(new Set())

const setStatus = throttle((unitId: number, date: string, isOpen: boolean) => {
    if (!unitId) return
    loadingDatesStatus.value.add(date)

    router.put(
        route('calendar.update', unitId),
        { date, is_open: isOpen },
        {
            preserveScroll: false,
            onSuccess: () => {
                const target = calendarDays.value.find((d) => d.date === date)
                if (target) target.is_open = isOpen

                toast('Calendar updated', {
                    description: 'The calendar has been updated successfully',
                    action: { label: 'Close' },
                })
            },
            onError: () => {
                toast('Error updating calendar', {
                    description: 'Something went wrong, please try again',
                    action: { label: 'Close' },
                })
            },
            onFinish: () => {
                loadingDatesStatus.value.delete(date)
                loadingDatesStatus.value = new Set([...loadingDatesStatus.value])
            },
        }
    )
}, 300)

const _setQty = (unitId: number, date: string, qty: number, oldQty?: number) => {
    if (!unitId) return
    loadingDatesQty.value.add(date)

    router.put(
        route('calendar.update', unitId),
        { date, qty },
        {
            preserveScroll: false,
            onSuccess: () => {
                const target = calendarDays.value.find((d) => d.date === date)
                if (target) target.qty = qty

                toast('Calendar updated', {
                    description: 'The calendar has been updated successfully',
                    action: { label: 'Close' },
                })
            },
            onError: () => {
                const target = calendarDays.value.find((d) => d.date === date)
                if (target && oldQty !== undefined) {
                    target.qty = oldQty
                    target.localValue = oldQty
                }

                toast('Invalid quantity', {
                    description: 'Quantity exceeds available capacity',
                    class: 'toast-destructive',
                    action: { label: 'Close' },
                })
            },
            onFinish: () => {
                loadingDatesQty.value.delete(date)
                loadingDatesQty.value = new Set([...loadingDatesQty.value])
            },
        }
    )
}

const setQty = debounce((unitId: number, date: string, qty: number, oldQty?: number) => {
    _setQty(unitId, date, qty, oldQty)
}, 300)

const startEditing = async (day: CalendarDay) => {
    calendarDays.value.forEach((d) => {
        if (d !== day) {
            const casted = Number(d.localValue)
            const newQty = Number.isNaN(casted) ? 0 : casted
            if (newQty !== d.qty && selectedUnitId.value) {
                save(d, selectedUnitId.value)
            }
            d.editing = false
        }
    })

    day.localValue = day.qty ?? 0
    day.editing = true
    await nextTick()
    inputRefs.get(day.date)?.focus()
}

const save = (day: CalendarDay, unitId: number) => {
    if (!day.editing) return
    const casted = Number(day.localValue)
    const newQty = Number.isNaN(casted) ? 0 : casted

    if (newQty === day.qty) {
        day.editing = false
        return
    }

    const oldQty = day.qty
    day.editing = false
    day.qty = newQty

    setQty(unitId, day.date, newQty, oldQty)
    setQty.flush?.()
}

const loadingRates = ref<Set<string>>(new Set())

const setRatePrice = debounce(
	(unitId: number, date: string, rateId: number, price: number, oldPrice?: number) => {
		if (!unitId) return
		const key = `${date}:${rateId}`
		loadingRates.value.add(key)

		router.put(
			route('calendar.update', unitId),
			{ date, rate_id: rateId, price },
			{
				preserveScroll: false,
				onSuccess: () => {
					const target = calendarDays.value.find((d) => d.date === date)
					if (target?.rates?.[rateId]) {
						target.rates[rateId].price = price
					}

					toast('Rate updated', {
						description: 'The rate has been updated successfully',
						action: { label: 'Close' },
					})
				},
				onError: () => {
					const target = calendarDays.value.find((d) => d.date === date)
					if (target?.rates?.[rateId] && oldPrice !== undefined) {
						target.rates[rateId].price = oldPrice
						target.rates[rateId].localValue = oldPrice
					}

					toast('Invalid price', {
						description: 'Something went wrong, please try again',
						class: 'toast-destructive',
						action: { label: 'Close' },
					})
				},
				onFinish: () => {
					loadingRates.value.delete(key)
					loadingRates.value = new Set([...loadingRates.value])
				},
			}
		)
	},
	300
)
/* ------------------------------ Misc ------------------------------ */
const closeModal = (reservationId: number) => {
    if (reservationId !== undefined && reservationId !== null) {
        dialogStates[reservationId] = false
    }
}

const handleClickOutside = (e: MouseEvent) => {
    calendarDays.value.forEach((day) => {
        const inputEl = inputRefs.get(day.date)
        if (day.editing && inputEl && !inputEl.contains(e.target as Node)) {
            const casted = Number(day.localValue)
            const newQty = Number.isNaN(casted) ? 0 : casted
            if (newQty !== day.qty && selectedUnitId.value) {
                save(day, selectedUnitId.value)
            }
            day.editing = false
        }
    })
}

/* ------------------------------ Lifecycle ------------------------------ */
onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    setQty.flush?.()
    document.removeEventListener('click', handleClickOutside)
})

/* ------------------------------ Utilities ------------------------------ */
const formatNumber = (num: number | string | undefined) => {
    const n = Number(num ?? 0)
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(n)
}
</script>

<template>
	<Head title="Calendar" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
			<!-- Controls -->
			<div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
				<!-- Unit combobox -->
				<Combobox v-model="selectedUnit" class="w-full md:max-w-[250px]">
					<ComboboxAnchor as-child>
						<ComboboxTrigger as-child>
							<Button
								type="button"
								variant="outline"
								class="justify-between w-full"
								:class="{ 'font-normal text-muted-foreground': !selectedUnit?.value }"
							>
								<span class="truncate">{{ selectedUnit?.label ?? 'Select a unit' }}</span>
								<ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
							</Button>
						</ComboboxTrigger>

						<ComboboxList align="start" class="w-full min-w-[250px]">
							<div class="relative w-full max-w-sm items-center combobox-input-wrapper">
								<ComboboxInput v-model="comboboxUnitSearchTerm" placeholder="Search unit..." />
								<span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
									<Search class="size-4 text-muted-foreground" />
								</span>
							</div>

							<ComboboxGroup :class="units.length < 1 ? 'p-0 border-none' : 'border-t'">
								<ComboboxItem v-for="unit in units" :key="unit.value" :value="unit">
									{{ unit.label }}
									<ComboboxItemIndicator>
										<Check :class="cn('ml-auto h-4 w-4')" />
									</ComboboxItemIndicator>
								</ComboboxItem>
							</ComboboxGroup>
						</ComboboxList>
					</ComboboxAnchor>
				</Combobox>

				<!-- From -->
				<Popover v-model:open="openFrom">
					<PopoverTrigger as-child>
						<Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
							<CalendarIcon />
							<span>From</span>
							<div v-if="fromDate" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
								<Badge variant="secondary" class="font-normal rounded-sm px-1.5">
									{{ dayjs(fromDate.toDate(getLocalTimeZone())).format('MMM DD, YYYY') }}
								</Badge>
							</div>
						</Button>
					</PopoverTrigger>
					<PopoverContent align="start" class="w-auto p-0">
						<Calendar v-model="fromDate" initial-focus :is-date-disabled="disablePastDates" @update:model-value="handleFromSelect" />
					</PopoverContent>
				</Popover>

				<!-- Until -->
				<Popover v-model:open="openTo">
					<PopoverTrigger as-child>
						<Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
							<CalendarIcon />
							<span>Until</span>
							<div v-if="toDate" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
								<Badge variant="secondary" class="font-normal rounded-sm px-1.5">
									{{ dayjs(toDate.toDate(getLocalTimeZone())).format('MMM DD, YYYY') }}
								</Badge>
							</div>
						</Button>
					</PopoverTrigger>
					<PopoverContent align="start" class="w-auto p-0">
						<Calendar v-model="toDate" initial-focus :is-date-disabled="disableUntilDates" @update:model-value="handleToSelect" />
					</PopoverContent>
				</Popover>
			</div>

			<!-- Grid -->
			<div class="overflow-hidden rounded-lg border h-full">
				<div class="relative w-full flex h-full">
					<!-- Left labels -->
					<div class="w-full max-w-[250px] shrink-0 text-sm divide-y bg-background border-e">
						<div class="px-5 h-16 flex items-center">
							<span class="block w-full font-medium">Date</span>
						</div>
						<div class="px-5 h-[52px] flex items-center"><span class="block w-full">Unit status</span></div>
						<div class="px-5 h-[52px] flex items-center"><span class="block w-full">Units to sell</span></div>
						<div class="px-5 h-[52px] flex items-center"><span class="block w-full">Units booked</span></div>

						<!-- Rate labels -->
						<div
							v-for="rate in (selectedUnit?.rates ?? [])"
							:key="rate.value"
							class="px-5 h-[52px] flex items-center gap-2 text-muted-foreground"
						>
							<Tag class="h-4 w-4" />
							<span class="w-full leading-none">{{ rate.label }}</span>
						</div>

						<!-- Slot labels by qty -->
						<template v-if="selectedUnit?.qty && selectedUnit.qty > 1">
							<div
								v-for="(_q, idx) in selectedUnit.qty"
								:key="idx"
								class="px-5 h-[52px] flex items-center last:border-b"
							>
								<div class="flex items-baseline gap-1.5 w-full">
									<span class="line-clamp-1">{{ selectedUnit?.name ?? selectedUnit?.label }}</span>
									<span class="text-[10px] text-muted-foreground">{{ (idx + 1).toString().padStart(2, '0') }}</span>
								</div>
							</div>
						</template>
						<template v-else>
							<div class="px-5 h-[52px] flex items-center last:border-b">
								<div class="flex items-baseline gap-1.5 w-full text-xs">
									<span class="line-clamp-1">{{ selectedUnit?.name ?? selectedUnit?.label ?? '—' }}</span>
								</div>
							</div>
						</template>
					</div>

					<!-- Right grid -->
					<div v-if="!isLoadingFetchCalendarDays" class="text-sm overflow-x-auto bg-background">
						<div class="bg-background w-fit overflow-x-hidden">
							<!-- Date header -->
							<div class="flex items-center shrink-0 divide-x w-max border-b h-16">
								<div
									v-for="date in calendarDays"
									:key="date.date"
									class="text-xs w-20 shrink-0 text-right px-2.5 py-1.5 h-full hover:bg-muted"
									:class="{
										'bg-muted/50': date.is_weekend,
										'border-muted-foreground/70 border-dashed': date.is_month_end,
										'bg-red-100 hover:bg-red-100 text-red-700/30': !date.is_open,
										'bg-muted hover:bg-muted text-muted-foreground/30': date.qty === 0,
									}"
								>
									<div class="font-medium">{{ date.day }}</div>
									<div class="pt-1 text-[10px]">{{ date.day_number }} {{ date.month }}</div>
									<div
										class="pt-0.5 text-[10px]"
										:class="{ 'text-red-700/30': !date.is_open, 'text-muted-foreground/30': date.qty === 0 }"
									>
										{{ date.year }}
									</div>
								</div>
							</div>

							<!-- Unit status row -->
							<div class="flex items-center shrink-0 divide-x w-max border-b h-[52px]">
								<div
									v-for="date in calendarDays"
									:key="date.date + ':status'"
									class="w-20 h-full text-xs px-1.5 flex items-center"
									:class="{
										'bg-muted/50': date.is_weekend,
										'border-muted-foreground/70 border-dashed': date.is_month_end,
										'bg-red-100 hover:bg-red-100 text-red-700/30': !date.is_open,
										'bg-muted hover:bg-muted text-muted-foreground/30': date.qty === 0 && date.is_open,
									}"
								>
									<Button
										type="button"
										size="sm"
										class="w-full text-xs flex items-center justify-center hover:cursor-pointer"
										:variant="!date.is_open ? 'destructive' : undefined"
										@click="selectedUnitId && setStatus(selectedUnitId, date.date, !date.is_open)"
										:disabled="loadingDatesStatus.has(date.date) || (!date.is_open ? false : date.qty === 0) || !selectedUnitId"
									>
										<LoaderCircle
											v-if="loadingDatesStatus.has(date.date)"
											class="!w-3.5 !h-3.5 animate-spin"
										/>
										<span class="block w-full overflow-hidden whitespace-nowrap text-ellipsis text-center">
											<template v-if="!date.is_open">Closed</template>
											<template v-else-if="date.qty === 0">Sold out</template>
											<template v-else>Open</template>
										</span>
									</Button>
								</div>
							</div>

							<!-- Units to sell row -->
							<div class="flex items-center shrink-0 divide-x w-max border-b h-[52px]">
								<div
									v-for="date in calendarDays"
									:key="date.date + ':qty'"
									class="w-20 h-full text-xs px-1.5 flex items-center hover:bg-muted relative"
									:class="{
										'bg-muted/50': date.is_weekend,
										'border-muted-foreground/70 border-dashed': date.is_month_end,
										'bg-red-100 hover:bg-red-100 text-red-700/30': !date.is_open,
										'bg-muted hover:bg-muted text-muted-foreground/30': date.qty === 0,
									}"
								>
									<LoaderCircle
										v-if="loadingDatesQty.has(date.date)"
										class="!w-3.5 !h-3.5 animate-spin absolute ms-1"
									/>

									<span
										v-if="!date.editing && !date.saving"
										class="flex items-center justify-end w-full text-right border border-transparent hover:border-border hover:bg-background h-5 pe-0.5 hover:cursor-text"
										@click.stop="startEditing(date)"
									>
										{{ date.qty }}
									</span>

									<input
										v-else
										:disabled="date.saving || loadingDatesQty.has(date.date)"
										:name="date.date"
										:ref="(el) => el && inputRefs.set(date.date, el)"
										v-model="date.localValue"
										type="number"
										min="0"
										@keyup.enter="selectedUnitId && save(date, selectedUnitId)"
										style="font-size: 12px !important;"
										class="w-full border bg-background text-right h-5 disabled:bg-muted disabled:text-foreground/40"
									/>
								</div>
							</div>

							<!-- Units booked row -->
							<div class="flex items-center shrink-0 divide-x w-max border-b h-[52px]">
								<div
									v-for="date in calendarDays"
									:key="date.date + ':booked'"
									class="w-20 h-full text-xs px-1.5 flex items-center hover:bg-muted"
									:class="{
										'bg-muted/50': date.is_weekend,
										'border-muted-foreground/70 border-dashed': date.is_month_end,
										'bg-red-100 hover:bg-red-100 text-red-700/30': !date.is_open,
										'bg-muted hover:bg-muted text-muted-foreground/30': date.qty === 0,
									}"
								>
									<Badge
										variant="secondary"
										class="inline-flex w-full min-w-0 text-[10px] font-normal"
										:class="{ 'bg-red-200': !date.is_open }"
									>
										<span
											class="block w-full overflow-hidden whitespace-nowrap text-ellipsis text-right"
											:class="!date.is_open ? 'text-red-700/30' : 'text-muted-foreground'"
										>
											{{ date.reservations_count ?? 0 }}
										</span>
									</Badge>
								</div>
							</div>

							<!-- Rate rows -->
							<template v-if="selectedUnit?.rates && selectedUnit.rates.length">
								<div
									v-for="rate in selectedUnit.rates"
									:key="'rate-row-' + rate.value"
									class="flex items-center shrink-0 divide-x w-max border-b h-[52px]"
								>
									<div
										v-for="date in calendarDays"
										:key="date.date + ':rate:' + rate.value"
										class="w-20 h-full text-xs px-1.5 flex items-center hover:bg-muted relative"
										:class="{
											'bg-muted/50': date.is_weekend,
											'border-muted-foreground/70 border-dashed': date.is_month_end,
											'bg-red-100 hover:bg-red-100 text-red-700': !date.is_open,
											'bg-muted hover:bg-muted text-muted-foreground/30': date.qty === 0,
										}"
									>
										<LoaderCircle
											v-if="loadingRates.has(date.date + ':' + rate.value)"
											class="!w-3.5 !h-3.5 animate-spin absolute ms-1"
										/>

										<!-- Display -->
										<span
											v-if="!date.rates?.[rate.value]?.editing"
											class="flex items-center justify-end w-full text-right border border-transparent hover:border-border hover:bg-background h-5 pe-0.5 hover:cursor-text"
											@click.stop="
												() => {
													if (!date.rates) date.rates = {}
													if (!date.rates[rate.value]) date.rates[rate.value] = { price: rate.price }
													date.rates[rate.value].localValue = date.rates[rate.value].price ?? rate.price
													date.rates[rate.value].editing = true
												}
											"
										>
											{{ formatNumber(date.rates?.[rate.value]?.price ?? rate.price) }}
										</span>

										<!-- Edit -->
										<input
											v-else
											:disabled="loadingRates.has(date.date + ':' + rate.value)"
											v-model="date.rates[rate.value].localValue"
											type="number"
											min="0"
											@keyup.enter="
												() => {
													const casted = Number(date.rates?.[rate.value]?.localValue)
													const newPrice = Number.isNaN(casted) ? 0 : casted
													const oldPrice = date.rates?.[rate.value]?.price ?? rate.price
													date.rates[rate.value].editing = false
													date.rates[rate.value].price = newPrice
													selectedUnitId &&
														setRatePrice(selectedUnitId, date.date, Number(rate.value), newPrice, oldPrice)
												}
											"
											@blur="
												() => {
													const casted = Number(date.rates?.[rate.value]?.localValue)
													const newPrice = Number.isNaN(casted) ? 0 : casted
													const oldPrice = date.rates?.[rate.value]?.price ?? rate.price

													// hanya save kalau ada perubahan
													if (newPrice !== oldPrice) {
														date.rates[rate.value].editing = false
														date.rates[rate.value].price = newPrice
														selectedUnitId &&
															setRatePrice(selectedUnitId, date.date, Number(rate.value), newPrice, oldPrice)
													} else {
														date.rates[rate.value].editing = false
													}
												}
											"
											style="font-size: 12px !important;"
											class="w-full border bg-background text-right h-5 disabled:bg-muted disabled:text-foreground/40"
										/>
									</div>
								</div>
							</template>

							<!-- Reservation strips per slot -->
							<template v-if="selectedUnit?.qty && selectedUnit.qty > 0">
								<div
									v-for="(_, slotIndex) in selectedUnit.qty"
									:key="'slot-row-' + slotIndex"
									class="flex items-center shrink-0 divide-x w-max border-b h-[52px]"
								>
									<div
										v-for="date in calendarDays"
										:key="date.date + ':slot:' + slotIndex"
										class="w-20 h-full text-xs px-0 flex items-center relative"
										:class="{
											'bg-muted/50': date.is_weekend,
											'border-muted-foreground/70 border-dashed': date.is_month_end,
											'bg-red-100 hover:bg-red-100 text-red-700/30': !date.is_open,
											'bg-muted hover:bg-muted text-muted-foreground/30': date.qty === 0,
										}"
									>
										<Dialog
											v-if="date.reservations && date.reservations[slotIndex]"
											v-model:open="dialogStates[date.reservations[slotIndex].id]"
										>
											<TooltipProvider>
												<Tooltip>
													<TooltipTrigger
														class="absolute z-20"
														:class="date.reservations[slotIndex]?.is_first_day ? 'left-3' : ''"
													>
														<DialogTrigger as-child>
															<Button
																type="button"
																variant="primary"
																size="sm"
																class="block overflow-hidden text-ellipsis text-xs text-left z-20 absolute bg-chart-2 text-white hover:bg-teal-700 dark:hover:bg-emerald-600"
																:class="date.reservations[slotIndex]?.is_first_day ? 'rounded-s-md' : 'rounded-s-none border-s-0'"
																:style="{ width: date.reservations[slotIndex]?.btn_width }"
															>
																{{ date.reservations[slotIndex]?.name }}
															</Button>
															<div
																class="bg-background h-8 rounded-md"
																:style="{ width: date.reservations[slotIndex]?.btn_width }"
															>
																&nbsp;
															</div>
														</DialogTrigger>
													</TooltipTrigger>
													<TooltipContent>
														<p>{{ date.reservations[slotIndex]?.name }}</p>
													</TooltipContent>
												</Tooltip>
											</TooltipProvider>

											<DialogContent class="p-0">
												<DialogHeader class="px-6 pt-6 pb-0">
													<DialogTitle>{{ date.reservations[slotIndex]?.name }}</DialogTitle>
													<DialogDescription>
														2 guests
														<span class="mx-1">·</span>
														{{ date.reservations[slotIndex]?.nights_duration }}
														{{ (date.reservations[slotIndex]?.nights_duration ?? 1) > 1 ? 'nights' : 'night' }}
													</DialogDescription>
												</DialogHeader>

												<div class="border divide-y">
													<div class="flex items-center gap-2 text-sm px-6 py-4">
														<NotebookPen class="w-4 h-4" />
														<h1 class="leading-none font-medium">Booked on</h1>
														<div class="ms-auto">
															{{ dayjs(date.reservations[slotIndex]?.booked_on).format('MMM DD, YYYY') }}
														</div>
													</div>
													<div class="flex items-center gap-2 text-sm px-6 py-4">
														<CalendarIcon class="w-4 h-4" />
														<h1 class="leading-none font-medium">Check-in</h1>
														<div class="ms-auto">
															{{ dayjs(date.reservations[slotIndex]?.check_in).format('MMM DD, YYYY') }}
														</div>
													</div>
													<div class="flex items-center gap-2 text-sm px-6 py-4">
														<CalendarIcon class="w-4 h-4" />
														<h1 class="leading-none font-medium">Check out</h1>
														<div class="ms-auto">
															{{ dayjs(date.reservations[slotIndex]?.check_out).format('MMM DD, YYYY') }}
														</div>
													</div>
												</div>

												<DialogFooter class="px-6 pb-6 pt-0">
													<DialogClose as-child>
														<Button variant="outline" @click="closeModal(date.reservations?.[slotIndex]?.id!)">
															Close
														</Button>
													</DialogClose>
													<Link :href="route('reservations.edit', date.reservations?.[slotIndex]?.id)">
														<Button class="w-full md:w-auto">View Details</Button>
													</Link>
												</DialogFooter>
											</DialogContent>
										</Dialog>
									</div>
								</div>
							</template>
						</div>
					</div>

					<!-- Loading -->
					<div v-else class="w-full flex items-center justify-center">
						<div class="flex items-center justify-center gap-2">
							<LoaderCircle class="w-4 h-4 animate-spin" />
							<span class="text-sm">Loading calendar...</span>
						</div>
					</div>

					<!-- Placeholder -->
					<div v-if="calendarDays.length < 13" class="grow relative border-s hidden lg:block">
						<PlaceholderPattern />
					</div>
				</div>
			</div>
		</div>
	</AppLayout>
</template>
