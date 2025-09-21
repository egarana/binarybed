<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import { toast } from 'vue-sonner';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { debounce } from 'lodash-es'
import Combobox from '@/components/ui/combobox/Combobox.vue';
import ComboboxAnchor from '@/components/ui/combobox/ComboboxAnchor.vue';
import ComboboxTrigger from '@/components/ui/combobox/ComboboxTrigger.vue';
import { CalendarIcon, Check, ChevronsUpDown, Search, X } from 'lucide-vue-next';
import ComboboxList from '@/components/ui/combobox/ComboboxList.vue';
import ComboboxInput from '@/components/ui/combobox/ComboboxInput.vue';
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue';
import ComboboxItem from '@/components/ui/combobox/ComboboxItem.vue';
import ComboboxItemIndicator from '@/components/ui/combobox/ComboboxItemIndicator.vue';
import { cn } from '@/lib/utils';
import Popover from '@/components/ui/popover/Popover.vue';
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue';
import PopoverContent from '@/components/ui/popover/PopoverContent.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import { today, getLocalTimeZone, parseDate, type DateValue } from "@internationalized/date"
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

dayjs.extend(utc)
dayjs.extend(timezone)

interface UnitOption {
    value: string;
    label: string;
    rates?: RateOption[];
}

interface RateOption {
    value: string;
    label: string;
}

interface CountryOption {
    country: string;
    countryName: string;
    code: string;
}

interface PhoneField {
    country: CountryOption;
    number: string;
}

const page = usePage();
const reservation = page.props.reservation as {
    id: number,
    unit: UnitOption | null,
    rate: RateOption | null,
    first_name: string,
    last_name: string,
    email: string,
    phone: PhoneField | null,
    check_in: string,
    check_out: string,
};

const units = ref<UnitOption[]>([])
const rates = ref<RateOption[]>(reservation.unit.rates || [])
const countries = ref<CountryOption[]>([])
const comboboxUnitSearchTerm = ref('')
const comboboxCountrySearchTerm = ref('')
const open = ref(false)
const openCheckIn = ref(false)
const openCheckOut = ref(false)
const checkInDate = ref<DateValue>(
    reservation.check_in ? parseDate(reservation.check_in) : undefined
)
const checkOutDate = ref<DateValue>(
    reservation.check_out ? parseDate(reservation.check_out) : undefined
)
const disabledDates = ref<string[]>(Array.isArray(page.props.disabledDates) 
    ? page.props.disabledDates 
    : [])

const selectedUnit = ref<UnitOption | undefined>(reservation.unit || undefined)
const selectedCountry = ref<CountryOption>(reservation.phone?.country);

const form = useForm({
    unit: reservation.unit,
    rate: reservation.rate,
    first_name: reservation.first_name,
    last_name: reservation.last_name,
    email: reservation.email,
    phone: {
        country: selectedCountry.value,
        number: reservation.phone?.number,
    } as PhoneField,
    check_in: reservation.check_in,
    check_out: reservation.check_out,
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reservations',
        href: '/reservations',
    },
    {
        title: 'Edit Reservation',
        href: route('reservations.edit', reservation.id)
    },
];

const submit = () => {
    form.put(route('reservations.update', reservation.id), {
        preserveScroll: false,
        onSuccess: () => {
            toast('Reservation updated', {
                description: 'The reservation has been updated successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: (errors) => {
            toast('Error updating reservation', {
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

const fetchCountriesComboboxData = debounce(() => {
    router.get(route('reservations.edit', reservation.id), {
        search: comboboxCountrySearchTerm.value
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['countries'],
        onSuccess: () => {
            const newProps = usePage().props.countries as CountryOption[] ?? []
            countries.value = newProps
        }
    })
}, 300)

watch(comboboxCountrySearchTerm, (term) => {
    if (!term) {
        countries.value = []
        return
    }

    fetchCountriesComboboxData()
})

const fetchUnitsComboboxData = debounce(() => {
    router.get(route('reservations.edit', reservation.id), {
        search: comboboxUnitSearchTerm.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['units'],
        onSuccess: () => {
            const newProps = usePage().props.units as UnitOption[] ?? []
            units.value = newProps
        }
    })
}, 300)

watch(comboboxUnitSearchTerm, (term) => {
    if (!term) {
        units.value = []
        return
    }

    fetchUnitsComboboxData()
})

watch(selectedUnit, (newVal) => {
    form.unit = newVal ?? null
    if (newVal) {
        const unit = units.value.find(u => u.value === newVal.value)
        rates.value = unit?.rates ?? []
        form.rate = rates.value.length > 0 ? rates.value[0] : null

        router.get(route('reservations.edit', reservation.id), {
            unit_id: newVal.value,
        }, {
            preserveScroll: true,
            preserveState: true,
            only: ['disabledDates'],
            onSuccess: () => {
                const newProps = usePage().props.disabledDates
                disabledDates.value = Array.isArray(newProps) ? newProps : []
            }
        })
    } else {
        rates.value = []
        form.rate = null
        disabledDates.value = []
    }

    // 🆕 Reset check-in & check-out
    checkInDate.value = undefined
    checkOutDate.value = undefined
    form.check_in = ''
    form.check_out = ''
})

watch(selectedCountry, (newVal) => {
    if (newVal) {
        form.phone.country = newVal
    }
})

// Disable past dates for check-in
const disableDates = (date: DateValue) => {
    const formatted = date.toString()
    return (date.compare(today(getLocalTimeZone())) < 0 || // past dates
        disabledDates.value.includes(formatted) // closed dates
    )
}

const nextDisabledDate = computed(() => {
    if (!checkInDate.value) return null
    const sorted = [...disabledDates.value].sort()
    return sorted.find(d => d > checkInDate.value.toString()) ? parseDate(sorted.find(d => d > checkInDate.value.toString()) !) : null
})

// Disable past dates **and today** for check-out
const disableCheckOutDates = (date: DateValue) => {
    const todayDate = today(getLocalTimeZone())
    if (date.compare(todayDate) <= 0) return true
    if (!checkInDate.value) return true
    if (date.compare(checkInDate.value) <= 0) return true
    // If there's a disabled boundary, checkout must be BEFORE it
    if (nextDisabledDate.value && date.compare(nextDisabledDate.value) > 0) {
        return true
    }
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

const MAX_PHONE_LENGTH = 15

function onPhoneInput(e: Event) {
    let input = (e.target as HTMLInputElement).value

    // Remove non-digit characters
    input = input.replace(/\D/g, '')

    // Remove leading zeros
    input = input.replace(/^0+/, '')

    // Limit max length
    if (input.length > MAX_PHONE_LENGTH) {
        input = input.slice(0, MAX_PHONE_LENGTH)
    }

    form.phone.number = input
}

watch(checkInDate, (newCheckIn) => {
    if (!newCheckIn) {
        checkOutDate.value = undefined
        form.check_out = ''
        return
    }
    // Find next blocked date
    if (nextDisabledDate.value) {
        // Checkout = the day before blocked date
        const prevDay = nextDisabledDate.value.subtract({
            days: 1
        })
        // Only valid if prevDay > checkIn
        if (prevDay.compare(newCheckIn) > 0) {
            checkOutDate.value = prevDay
            form.check_out = dayjs(prevDay.toString()).format('YYYY-MM-DD')
        } else {
            // ❌ Check-in itself is invalid (next day blocked)
            checkOutDate.value = undefined
            form.check_out = ''
        }
    } else {
        // No blocked dates → default +1 day
        const nextDay = newCheckIn.add({
            days: 1
        })
        checkOutDate.value = nextDay
        form.check_out = dayjs(nextDay.toString()).format('YYYY-MM-DD')
    }
})
</script>

<template>
    <Head title="Edit Reservation" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <pre class="text-xs">
            {{ form }}
        </pre> -->
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex flex-col space-y-6 min-h-full">
                <HeadingSmall title="Edit reservation" description="Modify guest information and save changes to this reservation" />

                <Separator />
    
                <form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
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
                                <X class="mt-0.5"/>
                            </Button>
                        </div>
                        <InputError :message="form.errors.unit" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Rate</Label>
                        <Combobox v-model="form.rate" :disabled="!selectedUnit" class="mt-1">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="justify-between w-full"
                                        :class="{ 'font-normal text-muted-foreground': !form.rate }"
                                    >
                                        {{ form.rate?.label ?? 'Select a rate for the unit' }}
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

                    <div class="grid gap-2">
                        <Label>Check-in</Label>
                        <Popover v-model:open="openCheckIn">
                            <PopoverTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="justify-between w-full mt-1"
                                    :class="{ 'font-normal text-muted-foreground': !form.check_in }"
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

                    <div class="grid gap-2">
                        <Label>Check-out</Label>
                        <Popover v-model:open="openCheckOut">
                            <PopoverTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="justify-between w-full mt-1"
                                    :class="{ 'font-normal text-muted-foreground': !form.check_out }"
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

                    <div class="grid gap-2">
                        <Label for="firstName">First Name</Label>
                        <Input id="firstName" class="mt-1 block w-full" v-model="form.first_name" autocomplete="name" placeholder="Guest first name (e.g. John)" />
                        <InputError :message="form.errors.first_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="lastName">Last Name</Label>
                        <Input id="lastName" class="mt-1 block w-full" v-model="form.last_name" autocomplete="name" placeholder="Guest last name (e.g. Doe)" />
                        <InputError :message="form.errors.last_name" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" autocomplete="email" placeholder="Guest email (e.g. johndoe@example.com)" />
                        <InputError :message="form.errors.email" />
                    </div>

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

                    <div class="mt-auto text-right">
                        <Button :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
