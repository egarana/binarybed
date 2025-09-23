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

import { CalendarIcon, Check, ChevronsUpDown, Search, X } from 'lucide-vue-next'

import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

import {
	today,
	getLocalTimeZone,
	type DateValue,
	parseDate
} from '@internationalized/date'

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

const datesWithQty = ref<{ date: string; qty: number }[]>(   // ⬅️ ADD
	Array.isArray(usePage().props.datesWithQty)
		? usePage().props.datesWithQty
		: []
)
const minQty = ref<number | null>(usePage().props.minQty ?? null)

const selectedUnit = ref<UnitOption | undefined>()
const selectedCountry = ref<CountryOption>({
	country: 'ID',
	countryName: 'Indonesia',
	code: '+62'
})

/* ------------------------------ Form ------------------------------ */
const form = useForm({
	unit: null as UnitOption | null,
	rate: null as RateOption | null,
	first_name: '',
	last_name: '',
	email: '',
	phone: {
		country: selectedCountry.value,
		number: ''
	} as PhoneField,
	check_in: '',
	check_out: '',
	qty: '',
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
				action: { label: 'Close' }
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
				only: ['units', 'datesWithQty', 'minQty'], // ⬅️ tambahin
				onSuccess: () => {
					// update rates
					const newUnits = usePage().props.units as UnitOption[] ?? []
					const foundUnit = newUnits.find((u) => u.value === unit.value)

					rates.value = foundUnit?.rates ?? []
					form.rate = rates.value.length > 0 ? rates.value[0] : null

					// update qty info
					datesWithQty.value = usePage().props.datesWithQty as { date: string; qty: number }[] ?? []
					minQty.value = usePage().props.minQty as number ?? null
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
</script>

<template>
    <!-- <pre class="text-xs absolute z-50 bg-white">{{ disabledDates }}</pre> -->
	<Head title="Create Reservation" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
			<div class="flex flex-col space-y-6 min-h-full">
				<HeadingSmall
					title="Create reservation"
					description="Manually create a new reservation for a unit and assign the appropriate rate"
				/>


				<pre class="text-xs bg-muted p-2 rounded">
					{{ datesWithQty }}
					{{ minQty }}
				</pre>

				<Separator />

				<form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
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

							<Button type="button" variant="outline" @click="selectedUnit = undefined">
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
                                        v-for="(item, index) in minQty"
                                        :key="index"
                                        :value="item"
                                        class="w-full min-w-[250px] flex items-center justify-between"
                                    >
                                        {{ item }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.qty" />
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

					<!-- Save -->
					<div class="mt-auto text-right">
						<Button :disabled="form.processing">Save</Button>
					</div>
				</form>
			</div>
		</div>
	</AppLayout>
</template>
