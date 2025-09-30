<script setup lang="ts">
/* ------------------------------ Imports ------------------------------ */
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'

import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import { Separator } from '@/components/ui/separator'

import Combobox from '@/components/ui/combobox/Combobox.vue'
import ComboboxAnchor from '@/components/ui/combobox/ComboboxAnchor.vue'
import ComboboxTrigger from '@/components/ui/combobox/ComboboxTrigger.vue'
import ComboboxList from '@/components/ui/combobox/ComboboxList.vue'
import ComboboxInput from '@/components/ui/combobox/ComboboxInput.vue'
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue'
import ComboboxItem from '@/components/ui/combobox/ComboboxItem.vue'
import ComboboxItemIndicator from '@/components/ui/combobox/ComboboxItemIndicator.vue'

import Popover from '@/components/ui/popover/Popover.vue'
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue'
import PopoverContent from '@/components/ui/popover/PopoverContent.vue'

import Calendar from '@/components/ui/calendar/Calendar.vue'

import { cn } from '@/lib/utils'
import { type BreadcrumbItem } from '@/types'

import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { debounce } from 'lodash-es'

import { CalendarIcon, Check, ChevronRight, ChevronsUpDown, Search, X } from 'lucide-vue-next'

import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

import {
	today,
	getLocalTimeZone,
	type DateValue,
	parseDate
} from '@internationalized/date'

import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	DialogTrigger,
} from '@/components/ui/dialog'
import DialogClose from '@/components/ui/dialog/DialogClose.vue'
import { Textarea } from '@/components/ui/textarea'

/* ------------------------------ Day.js Setup ------------------------------ */
dayjs.extend(utc)
dayjs.extend(timezone)

/* ------------------------------ Interfaces ------------------------------ */
interface RateOption {
	value: string
	label: string
}

interface UnitOption {
	value: string
	label: string
	rates: RateOption[]
}

interface CountryOption {
	country: string      // e.g., "ID"
	countryName: string  // e.g., "Indonesia"
	code: string         // e.g., "+62"
}

interface PhoneField {
	country: CountryOption
	number: string
}

/* ------------------------------ State ------------------------------ */
const units = ref<UnitOption[]>([])
const rates = ref<RateOption[]>([])
const countries = ref<CountryOption[]>([])

const comboboxUnitSearchTerm = ref('')
const comboboxCountrySearchTerm = ref('')

const open = ref(false)
const openCheckIn = ref(false)
const openCheckOut = ref(false)

const checkInDate = ref<DateValue>()
const checkOutDate = ref<DateValue>()

const disabledDates = ref<string[]>(
	Array.isArray(usePage().props.disabledDates)
		? usePage().props.disabledDates
		: []
)

const reservationDays = ref<{ date: string; qty: number }[]>(   // ⬅️ ADD
	Array.isArray(usePage().props.reservationDays)
		? usePage().props.reservationDays
		: []
)
const minQty = ref<number | null>(usePage().props.minQty ?? null)

const selectedUnit = ref<UnitOption | undefined>()
const selectedCountry = ref<CountryOption>({
	country: 'ID',
	countryName: 'Indonesia',
	code: '+62'
})

const totalPrices = ref<{ rate_id: number; rate_name: string; total: number }[]>(
	Array.isArray(usePage().props.totalPrices)
		? usePage().props.totalPrices
		: []
)

// Total aktif: ambil sesuai rate yang dipilih × qty
const activeTotal = computed(() => {
	if (!form.rate || !form.qty) return 0
	const found = totalPrices.value.find(tp => String(tp.rate_id) === String(form.rate?.value))
	return found ? found.total * Number(form.qty) : 0
})

const statuses = [
	{ value: 'pending', label: 'Pending' },
	{ value: 'confirmed', label: 'Confirmed' },
	{ value: 'cancelled', label: 'Cancelled' },
	{ value: 'checked_in', label: 'Checked In' },
	{ value: 'checked_out', label: 'Checked Out' },
]

const paymentStatuses = [
	{ value: 'unpaid', label: 'Unpaid' },
	{ value: 'paid', label: 'Paid' },
	{ value: 'refunded', label: 'Refunded' },
]

const sources = [
	{ value: 'direct', label: 'Direct (Website/Walk-in)' },
	{ value: 'agency', label: 'Travel Agency (Offline)' },
	{ value: 'ota', label: 'Online Travel Agent (Booking, Agoda, Airbnb, etc.)' },
	{ value: 'other', label: 'Other' },
]


/* ------------------------------ Form ------------------------------ */
const form = useForm({
	status: {
		value: 'pending', 
		label: 'Pending',
	},
	payment_status: {
		value: 'unpaid', 
		label: 'Unpaid',
	},
	source: {
		value: 'direct', 
		label: 'Direct (Website/Walk-in)',
	},
	unit: null as UnitOption | null,
	check_in: '',
	check_out: '',
	qty: '',
	guests: '',
	rate: null as RateOption | null,
	total_price: 0,
	currency: 'IDR',
	first_name: '',
	last_name: '',
	email: '',
	phone: {
		country: selectedCountry.value,
		number: ''
	} as PhoneField,
	notes: '',
})

/* ------------------------------ Breadcrumbs ------------------------------ */
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Reservations', href: '/reservations' },
	{ title: 'Create Reservation', href: '/reservations/create' }
]

/* ------------------------------ Methods ------------------------------ */
const submit = () => {
	form.post(route('reservations.store'), {
		preserveScroll: false,
		onSuccess: () => {
			toast('Reservation created', {
				description: 'The reservation has been created successfully',
				action: { label: 'Close' }
			})
		},
		onError: () => {
			toast('Error creating reservation', {
				description: 'Something went wrong, please try again',
				class: 'toast-destructive',
				action: { label: 'Close' },
			})
		}
	})
}

const handleKeydown = (e: KeyboardEvent) => {
	const isMac = navigator.platform.toUpperCase().includes('MAC')
	const isSaveShortcut =
		(isMac && e.metaKey && e.key === 's') ||
		(!isMac && e.ctrlKey && e.key === 's')

	if (isSaveShortcut) {
		e.preventDefault() // prevent browser's default "save" behavior
		submit()
	}
}

onMounted(() => {
	window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
	window.removeEventListener('keydown', handleKeydown)
})

/* ------------------------------ Fetch Combobox Data ------------------------------ */
const fetchCountriesComboboxData = debounce(() => {
	router.get(
		route('reservations.create'),
		{ search: comboboxCountrySearchTerm.value },
		{
			preserveScroll: true,
			preserveState: true,
			only: ['countries'],
			onSuccess: () => {
				const newProps = usePage().props.countries as CountryOption[] ?? []
				countries.value = newProps
			}
		}
	)
}, 300)

watch(comboboxCountrySearchTerm, (term) => {
	if (!term) {
		countries.value = []
		return
	}
	fetchCountriesComboboxData()
})

const fetchUnitsComboboxData = debounce(() => {
	router.get(
		route('reservations.create'),
		{ search: comboboxUnitSearchTerm.value },
		{
			preserveScroll: true,
			preserveState: true,
			only: ['units'],
			onSuccess: () => {
				const newProps = usePage().props.units as UnitOption[] ?? []
				units.value = newProps
			}
		}
	)
}, 300)

watch(comboboxUnitSearchTerm, (term) => {
	if (!term) {
		units.value = []
		return
	}
	fetchUnitsComboboxData()
})

/* ------------------------------ Watchers ------------------------------ */
watch(selectedUnit, (newVal) => {
	form.unit = newVal ?? null

	// Reset dulu
	rates.value = []
	form.rate = null
	disabledDates.value = []
	checkInDate.value = undefined
	checkOutDate.value = undefined
	form.check_in = ''
	form.check_out = ''
	form.qty = null
	form.total_price = 0

	// Fetch disabled dates kalau ada unit
	if (newVal) {
		router.get(
			route('reservations.create'),
			{ unit_id: newVal.value },
			{
				preserveScroll: true,
				preserveState: true,
				only: ['disabledDates'],
				onSuccess: () => {
					const newProps = usePage().props.disabledDates
					disabledDates.value = Array.isArray(newProps) ? newProps : []
				}
			}
		)
	}
})

watch([selectedUnit, checkInDate, checkOutDate], ([unit, checkIn, checkOut]) => {
	if (unit && checkIn && checkOut) {
		router.get(
			route('reservations.create'),
			{
				unit_id: unit.value,
				check_in: dayjs(checkIn.toString()).format('YYYY-MM-DD'),
				check_out: dayjs(checkOut.toString()).format('YYYY-MM-DD'),
			},
			{
				preserveScroll: true,
				preserveState: true,
				only: ['units', 'reservationDays', 'minQty', 'totalPrices'], // ⬅️ tambahin
				onSuccess: () => {
					// update rates
					const newUnits = usePage().props.units as UnitOption[] ?? []
					const foundUnit = newUnits.find((u) => u.value === unit.value)

					rates.value = foundUnit?.rates ?? []
					form.rate = rates.value.length > 0 ? rates.value[0] : null

					// update qty info
					reservationDays.value = usePage().props.reservationDays as { date: string; qty: number }[] ?? []
					minQty.value = usePage().props.minQty as number ?? null
					totalPrices.value = usePage().props.totalPrices as { rate_id: number; rate_name: string; total: number }[] ?? []
				}
			}
		)
	}
})

watch(selectedCountry, (newVal) => {
	if (newVal) {
		form.phone.country = newVal
	}
})

// Auto set/reset qty berdasarkan check-in & check-out
watch([checkInDate, checkOutDate], ([checkIn, checkOut]) => {
	if (checkIn && checkOut) {
		// Default qty = 1 setiap kali tanggal valid
		form.qty = 1
	} else {
		// Kalau salah satu kosong (misalnya checkout direset) → null
		form.qty = null
	}
})

watch(() => activeTotal.value, (newVal) => {
	form.total_price = newVal
})

watch(() => form.qty, (newQty) => {
	if (newQty && selectedUnit.value) {
		// otomatis isi guests jadi maksimum
		form.guests = newQty * (selectedUnit.value.occupancy ?? 1)
	}
})

/* ------------------------------ Date Handling ------------------------------ */
const disableDates = (date: DateValue) => {
	const formatted = date.toString()
	return (
		date.compare(today(getLocalTimeZone())) < 0 || // past dates
		disabledDates.value.includes(formatted)       // closed dates
	)
}

const nextDisabledDate = computed(() => {
	if (!checkInDate.value) return null
	const sorted = [...disabledDates.value].sort()
	return sorted.find(d => d > checkInDate.value.toString())
		? parseDate(sorted.find(d => d > checkInDate.value.toString())!)
		: null
})

const disableCheckOutDates = (date: DateValue) => {
	const todayDate = today(getLocalTimeZone())
	if (date.compare(todayDate) <= 0) return true
	if (!checkInDate.value) return true
	if (date.compare(checkInDate.value) <= 0) return true
	if (nextDisabledDate.value && date.compare(nextDisabledDate.value) > 0) return true
	return false
}

const handleCheckInSelect = (date: DateValue | undefined) => {
	if (!date) return

	checkInDate.value = date
	form.check_in = dayjs(date.toString()).format('YYYY-MM-DD')

	if (!checkOutDate.value || date.compare(checkOutDate.value) >= 0) {
		const nextDay = date.add({ days: 1 })
		checkOutDate.value = nextDay
		form.check_out = dayjs(nextDay.toString()).format('YYYY-MM-DD')
	}

	openCheckIn.value = false
}

const handleCheckOutSelect = (date: DateValue | undefined) => {
	if (!date) return

	checkOutDate.value = date
	form.check_out = dayjs(date.toString()).format('YYYY-MM-DD')

	if (!checkInDate.value || date.compare(checkInDate.value) <= 0) {
		const prevDay = date.subtract({ days: 1 })
		checkInDate.value = prevDay
		form.check_in = dayjs(prevDay.toString()).format('YYYY-MM-DD')
	}

	openCheckOut.value = false
}

/* ------------------------------ Phone Handling ------------------------------ */
const MAX_PHONE_LENGTH = 15

function onPhoneInput(e: Event) {
	let input = (e.target as HTMLInputElement).value

	input = input.replace(/\D/g, '')       // Remove non-digit characters
	input = input.replace(/^0+/, '')       // Remove leading zeros

	if (input.length > MAX_PHONE_LENGTH) {
		input = input.slice(0, MAX_PHONE_LENGTH)
	}

	form.phone.number = input
}

/* ------------------------------ Auto Adjust Checkout ------------------------------ */
watch(checkInDate, (newCheckIn) => {
	if (!newCheckIn) {
		checkOutDate.value = undefined
		form.check_out = ''
		return
	}

	if (nextDisabledDate.value) {
		const prevDay = nextDisabledDate.value.subtract({ days: 1 })
		if (prevDay.compare(newCheckIn) > 0) {
			checkOutDate.value = prevDay
			form.check_out = dayjs(prevDay.toString()).format('YYYY-MM-DD')
		} else {
			checkOutDate.value = undefined
			form.check_out = ''
		}
	} else {
		const nextDay = newCheckIn.add({ days: 1 })
		checkOutDate.value = nextDay
		form.check_out = dayjs(nextDay.toString()).format('YYYY-MM-DD')
	}
})

/* ------------------------------ Utilities ------------------------------ */
const formatNumber = (num: number | string | undefined) => {
    const n = Number(num ?? 0)
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(n)
}

const formatRupiah = (num: number | string | undefined) => {
	const n = Number(num ?? 0)

	const formatted = new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'IDR',
		minimumFractionDigits: 0
	}).format(n)

	return formatted.replace("IDR", "Rp")
}

const filteredReservationDays = computed(() => {
	if (!form.rate) return []
	const rid = String(form.rate.value)
	// ganti 'reservationDays' dengan sumber datamu, mis. datesWithQty
	return (reservationDays.value as any[]).filter(d => String(d.rate_id) === rid)
})
</script>

<template>
    <!-- <pre class="text-xs absolute z-50 bg-white">{{ selectedUnit }}</pre> -->
	<Head title="Create Reservation" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
			<div class="flex flex-col space-y-6 min-h-full">
				<HeadingSmall
					title="Create reservation"
					description="Manually create a new reservation for a unit and assign the appropriate rate"
				/>

				<!-- <pre class="text-xs bg-muted p-2 rounded">
					{{ form }}
				</pre> -->

				<Separator />

				<form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
					<!-- Status -->
                    <div class="grid gap-2">
                        <Label>Status</Label>
                        <Combobox 
                            v-model="form.status"
                            class="mt-1"
                        >
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                    >
                                        {{ form.status?.label ?? 'Select a status for the reservation' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList align="start" class="w-full min-w-[250px]">
                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="(status, index) in statuses"
                                        :key="index"
                                        :value="status"
                                        class="w-full min-w-[250px] flex items-center justify-between"
                                    >
                                        {{ status.label }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.status" />
                    </div>

					<!-- Payment Status -->
                    <div class="grid gap-2">
                        <Label>Payment Status</Label>
                        <Combobox 
                            v-model="form.payment_status"
                            class="mt-1"
                        >
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                    >
                                        {{ form.payment_status?.label ?? 'Select a payment status for the reservation' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList align="start" class="w-full min-w-[250px]">
                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="(status, index) in paymentStatuses"
                                        :key="index"
                                        :value="status"
                                        class="w-full min-w-[250px] flex items-center justify-between"
                                    >
                                        {{ status.label }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.payment_status" />
                    </div>

					<!-- Source -->
                    <div class="grid gap-2">
                        <Label>Source</Label>
                        <Combobox 
                            v-model="form.source"
                            class="mt-1"
                        >
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                    >
                                        {{ form.source?.label ?? 'Select a source for the reservation' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList align="start" class="w-full min-w-[250px]">
                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="(source, index) in sources"
                                        :key="index"
                                        :value="source"
                                        class="w-full min-w-[250px] flex items-center justify-between"
                                    >
                                        {{ source.label }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.source" />
                    </div>

					<!-- Unit -->
					<div class="grid gap-2">
						<Label>Unit</Label>
						<div class="mt-1 flex items-center gap-2">
							<Combobox v-model="selectedUnit" class="w-full">
								<ComboboxAnchor as-child>
									<ComboboxTrigger as-child>
										<Button
											type="button"
											variant="outline"
											class="justify-between w-full"
											:class="{ 'font-normal text-muted-foreground': !selectedUnit?.value }"
										>
											{{ selectedUnit?.label ?? 'Select a unit' }}
											<ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
										</Button>
									</ComboboxTrigger>
								</ComboboxAnchor>

								<ComboboxList align="start" class="w-full min-w-[200px]">
									<div class="relative w-full max-w-sm items-center combobox-input-wrapper">
										<ComboboxInput
											v-model="comboboxUnitSearchTerm"
											placeholder="Search unit..."
										/>
										<span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
											<Search class="size-4 text-muted-foreground" />
										</span>
									</div>

									<ComboboxGroup :class="units.length < 1 ? 'p-0 border-none' : 'border-t'">
										<ComboboxItem
											v-for="(unit, index) in units"
											:key="unit.value"
											:value="unit"
										>
											{{ unit.label }}
											<ComboboxItemIndicator>
												<Check :class="cn('ml-auto h-4 w-4')" />
											</ComboboxItemIndicator>
										</ComboboxItem>
									</ComboboxGroup>
								</ComboboxList>
							</Combobox>

							<Button v-if="selectedUnit" type="button" variant="outline" @click="selectedUnit = undefined">
								Clear
								<X class="mt-0.5" />
							</Button>
						</div>
						<InputError :message="form.errors.unit" />
					</div>

					<!-- Check-in -->
					<div class="grid gap-2">
						<Label>Check-in</Label>
						<Popover v-model:open="openCheckIn">
							<PopoverTrigger as-child>
								<Button
									type="button"
									variant="outline"
									class="justify-between w-full mt-1"
									:class="{ 'font-normal text-muted-foreground': !form.check_in }"
									:disabled="!selectedUnit"
								>
									{{ checkInDate ? dayjs(checkInDate).tz(dayjs.tz.guess()).format("DD MMM YYYY") : 'Select check-in date' }}
									<CalendarIcon class="ml-2 h-4 w-4 shrink-0 opacity-50 text-muted-foreground" />
								</Button>
							</PopoverTrigger>
							<PopoverContent align="start" class="w-auto p-0">
								<Calendar
									v-model="checkInDate"
									initial-focus
									:is-date-disabled="disableDates"
									@update:model-value="handleCheckInSelect"
								/>
							</PopoverContent>
						</Popover>
						<InputError :message="form.errors.check_in" />
					</div>

					<!-- Check-out -->
					<div class="grid gap-2">
						<Label>Check-out</Label>
						<Popover v-model:open="openCheckOut">
							<PopoverTrigger as-child>
								<Button
									type="button"
									variant="outline"
									class="justify-between w-full mt-1"
									:class="{ 'font-normal text-muted-foreground': !form.check_out }"
									:disabled="!selectedUnit || !checkInDate"
								>
									{{ checkOutDate ? dayjs(checkOutDate).tz(dayjs.tz.guess()).format("DD MMM YYYY") : 'Select check-out date' }}
									<CalendarIcon class="ml-2 h-4 w-4 shrink-0 opacity-50 text-muted-foreground" />
								</Button>
							</PopoverTrigger>
							<PopoverContent align="start" class="w-auto p-0">
								<Calendar
									v-model="checkOutDate"
									initial-focus
									:is-date-disabled="disableCheckOutDates"
									@update:model-value="handleCheckOutSelect"
								/>
							</PopoverContent>
						</Popover>
						<InputError :message="form.errors.check_out" />
					</div>

					<!-- Qty -->
                    <div class="grid gap-2">
                        <Label>Qty</Label>
                        <Combobox 
                            v-model="form.qty"
							:disabled="!selectedUnit || !checkInDate || !checkOutDate"
                            class="mt-1"
                        >
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                        :class="{ 'font-normal text-muted-foreground': !form.qty }"
                                    >
                                        {{ form.qty ? form.qty : 'Select a qty for the unit' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList align="start" class="w-full min-w-[250px]">
                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="(item, index) in selectedUnit.qty"
                                        :key="index"
                                        :value="item"
                                        class="w-full min-w-[250px] flex items-center justify-between"
										:disabled="item > minQty"
                                    >
                                        <span :class="item > minQty ? 'line-through text-muted-foreground/30' : ''">{{ item }}</span>
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.qty" />
                    </div>

					<!-- Guests -->
                    <div class="grid gap-2">
                        <Label>Guests</Label>
                        <Combobox 
                            v-model="form.guests"
							:disabled="!selectedUnit || !form.qty"
                            class="mt-1"
                        >
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                        :class="{ 'font-normal text-muted-foreground': !form.qty || !form.guests }"
                                    >
                                        {{ form.qty ? form.guests : 'Select a guest number for the reservation' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList align="start" class="w-full min-w-[250px]">
                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="(item, index) in (form.qty * selectedUnit.occupancy)"
                                        :key="index"
                                        :value="item"
                                        class="w-full min-w-[250px] flex items-center justify-between"
                                    >
                                        <span>{{ item }}</span>
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.guests" />
                    </div>

                    <!-- Rate -->
                    <div class="grid gap-2">
                        <Label>Rate</Label>
                        <Combobox 
                            v-model="form.rate" 
                            :disabled="!selectedUnit || !checkInDate || !checkOutDate" 
                            class="mt-1"
                        >
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                        :class="{ 'font-normal text-muted-foreground': !form.rate }"
                                    >
                                        {{ form.rate?.label ?? 'Select a rate for the reservation' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList align="start" class="w-full min-w-[250px]">
                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="(item, index) in rates"
                                        :key="index"
                                        :value="item"
                                        class="w-full min-w-[250px] flex items-center justify-between"
                                    >
                                        {{ item.label }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.rate" />
                    </div>

					<!-- Total Price -->
					<div class="grid gap-2">
						<Label for="totalPrice">Total Price</Label>
						<div class="mt-1 flex items-center gap-2">
							<Input
								readonly
								id="totalPrice"
								class="mt-1 block w-full"
								:value="form.total_price > 0 ? formatRupiah(form.total_price) : ''"
								placeholder="Total price"
							/>
							<Dialog v-if="form.total_price > 0">
								<DialogTrigger>
									<Button type="button" variant="outline">
										Breakdown
										<ChevronRight class="mt-0.5" />
									</Button>
								</DialogTrigger>
								<DialogContent class="p-0">
									<DialogHeader class="px-6 pt-6 pb-0">
										<DialogTitle>{{ form.rate?.label ?? 'Price breakdown' }}</DialogTitle>
										<DialogDescription>
											{{ dayjs(checkInDate).tz(dayjs.tz.guess()).format("DD MMM YYYY") }} - {{ dayjs(checkOutDate).tz(dayjs.tz.guess()).format("DD MMM YYYY") }}
										</DialogDescription>
									</DialogHeader>

									<div class="border divide-y" v-if="filteredReservationDays.length">
										<div 
											class="flex items-center justify-between gap-2 text-sm px-6 py-4" 
											v-for="day in filteredReservationDays"
											:key="`${day.date}-${day.rate_id}`"
										>
											<div>
												<h1 class="leading-none">
													{{ dayjs(day.date).tz(dayjs.tz.guess()).format("DD MMM YYYY") }}
												</h1>
												<div class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
													<span>{{ formatRupiah(day.total_with_tax) }} (incl. Tax 10%)</span>
													<div>
														x{{ form.qty }} {{ selectedUnit.type }}{{ form.qty > 1 ? 's' : '' }}
													</div>
												</div>
											</div>
											<div class="ms-auto">
												{{ formatRupiah(day.total_with_tax * (Number(form.qty) || 1)) }}
											</div>
										</div>
										<div
											class="flex items-center justify-between gap-2 text-sm px-6 py-[23px]" 
										>
											<h1 class="font-medium">
												Total price
											</h1>
											<div class="ms-auto font-semibold">
												{{ formatRupiah(form.total_price) }}
											</div>
										</div>
									</div>
									<div v-else class="border px-6 py-6 text-sm text-muted-foreground text-center">
										No price breakdown yet.
									</div>

									<DialogFooter class="px-6 pb-6 pt-0">
										<DialogClose as-child>
											<Button variant="outline">
												Close
											</Button>
										</DialogClose>
									</DialogFooter>
								</DialogContent>
							</Dialog>
						</div>
						<InputError :message="form.errors.total_price" />
					</div>

					<!-- Guest Info -->
					<div class="grid gap-2">
						<Label for="firstName">First Name</Label>
						<Input
							id="firstName"
							class="mt-1 block w-full"
							v-model="form.first_name"
							autocomplete="name"
							placeholder="Guest first name (e.g. John)"
						/>
						<InputError :message="form.errors.first_name" />
					</div>

					<div class="grid gap-2">
						<Label for="lastName">Last Name</Label>
						<Input
							id="lastName"
							class="mt-1 block w-full"
							v-model="form.last_name"
							autocomplete="name"
							placeholder="Guest last name (e.g. Doe)"
						/>
						<InputError :message="form.errors.last_name" />
					</div>

					<div class="flex flex-col gap-2">
						<Label for="email">Email</Label>
						<Input
							id="email"
							type="email"
							class="mt-1 block w-full"
							v-model="form.email"
							autocomplete="email"
							placeholder="Guest email (e.g. johndoe@example.com)"
						/>
						<InputError :message="form.errors.email" />
					</div>

					<!-- Phone -->
					<div class="grid gap-2">
						<Label for="phone">Phone</Label>
						<div class="mt-1 flex items-center justify-start gap-2">
							<Combobox v-model="selectedCountry">
								<ComboboxAnchor as-child>
									<ComboboxTrigger as-child>
										<Button
											type="button"
											variant="outline"
											class="justify-between w-[90px]"
										>
											{{ selectedCountry?.code ?? 'Select country code' }}
											<ChevronsUpDown class="ml-0 h-4 w-4 shrink-0 opacity-50" />
										</Button>
									</ComboboxTrigger>
								</ComboboxAnchor>

								<ComboboxList align="start" class="w-full min-w-[200px]">
									<div class="relative w-full max-w-sm items-center combobox-input-wrapper">
										<ComboboxInput
											v-model="comboboxCountrySearchTerm"
											placeholder="Search country..."
										/>
										<span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
											<Search class="size-4 text-muted-foreground" />
										</span>
									</div>

									<ComboboxGroup :class="countries.length < 1 ? 'p-0 border-none' : 'border-t'">
										<ComboboxItem
											v-for="(country, index) in countries"
											:key="country.country"
											:value="country"
										>
											{{ country.code }} ({{ country.country }}) - {{ country.countryName }}
											<ComboboxItemIndicator>
												<Check :class="cn('ml-auto h-4 w-4')" />
											</ComboboxItemIndicator>
										</ComboboxItem>
									</ComboboxGroup>
								</ComboboxList>
							</Combobox>

							<Input
								id="phone"
								v-model="form.phone.number"
								type="text"
								inputmode="numeric"
								placeholder="Enter phone number"
								maxlength="15"
								@input="onPhoneInput"
							/>
						</div>
						<InputError :message="form.errors['phone.number']" />
					</div>

					<!-- Notes -->
					<div class="grid gap-2">
						<Label for="notes">Notes</Label>
						<Textarea
							id="notes"
							class="mt-1 block w-full resize-none min-h-52"
							v-model="form.notes"
							autocomplete="notes"
							placeholder="Notes for this reservation"
						/>
						<InputError :message="form.errors.notes" />
					</div>

					<!-- Save -->
					<div class="mt-auto text-right">
						<Button :disabled="form.processing">Save</Button>
					</div>
				</form>
			</div>
		</div>
	</AppLayout>
</template>
