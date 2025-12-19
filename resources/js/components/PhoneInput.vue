<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger
} from '@/components/ui/combobox';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import InputError from '@/components/InputError.vue';
import countryCodesData from '@/data/country-codes.json';

interface Country {
    id: number;
    iso2: string;
    name: string;
    dialCode: string;
}

interface PhoneData {
    country: {
        country: string;
        countryName: string;
        code: string;
    };
    number: string;
}

interface Props {
    name: string;
    label?: string;
    modelValue?: PhoneData | null;
    tabindex?: number;
    required?: boolean;
    error?: string;
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Phone',
    required: false,
    placeholder: '81234567890',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: PhoneData | null): void;
}>();

// Country codes
const allCountries = ref<Country[]>(countryCodesData as Country[]);

// Popular countries (ISO2 codes) - shown by default to improve performance
const popularCountryISO2 = ['ID', 'US', 'AU', 'GB', 'DE'];

// Get popular countries
const popularCountries = computed(() =>
    allCountries.value.filter(c => popularCountryISO2.includes(c.iso2))
);

// Initialize from modelValue or default to Indonesia
const initCountry = props.modelValue?.country
    ? allCountries.value.find(c => c.iso2 === props.modelValue?.country?.country)
    : allCountries.value.find(c => c.iso2 === 'ID');

// State
const selectedCountry = ref<Country | null>(initCountry || allCountries.value[0]);
const phoneNumber = ref<string>(props.modelValue?.number || '');
const comboboxCountrySearchTerm = ref<string>('');

// Filtered countries - show popular by default, all when searching
const filteredCountries = computed(() => {
    if (!comboboxCountrySearchTerm.value) {
        return popularCountries.value;
    }

    const search = comboboxCountrySearchTerm.value.toLowerCase();
    return allCountries.value.filter(c =>
        c.name.toLowerCase().includes(search) ||
        c.iso2.toLowerCase().includes(search) ||
        c.dialCode.includes(search)
    );
});

// Phone JSON value for hidden input
const phoneJsonValue = computed(() => {
    if (!phoneNumber.value || !selectedCountry.value) return '';

    const phoneData: PhoneData = {
        country: {
            country: selectedCountry.value.iso2,
            countryName: selectedCountry.value.name,
            code: selectedCountry.value.dialCode
        },
        number: phoneNumber.value.replace(/\D/g, '') // Remove non-digits
    };

    return JSON.stringify(phoneData);
});

// Only allow numeric input
const onPhoneInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.value = target.value.replace(/\D/g, '');
    phoneNumber.value = target.value;
};

// Watch for changes and emit
watch([selectedCountry, phoneNumber], () => {
    if (!phoneNumber.value || !selectedCountry.value) {
        emit('update:modelValue', null);
        return;
    }

    emit('update:modelValue', {
        country: {
            country: selectedCountry.value.iso2,
            countryName: selectedCountry.value.name,
            code: selectedCountry.value.dialCode
        },
        number: phoneNumber.value.replace(/\D/g, '')
    });
});

// Watch modelValue changes (for external updates and initial value)
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        const country = allCountries.value.find(c => c.iso2 === newValue.country?.country);
        if (country) {
            selectedCountry.value = country;
        }
        phoneNumber.value = newValue.number || '';
    } else {
        phoneNumber.value = '';
    }
}, { deep: true, immediate: true });
</script>

<template>
    <div class="grid gap-2">
        <Label :for="name">{{ label }}</Label>
        <div class="mt-1 flex items-center justify-start gap-2">
            <!-- Country Combobox -->
            <Combobox v-model="selectedCountry">
                <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button
                            type="button"
                            variant="outline"
                            class="justify-between w-[100px]"
                        >
                            {{ selectedCountry?.dialCode ?? 'Select' }}
                            <ChevronsUpDown class="ml-0 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                </ComboboxAnchor>

                <ComboboxList align="start" class="w-full min-w-[300px]">
                    <div class="relative w-full max-w-sm items-center combobox-input-wrapper">
                        <ComboboxInput
                            v-model="comboboxCountrySearchTerm"
                            placeholder="Search country..."
                            class="combobox-search-input"
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>

                    <ComboboxGroup :class="filteredCountries.length < 1 ? 'p-0 border-none' : 'border-t'">
                        <ComboboxItem
                            v-for="country in filteredCountries"
                            :key="country.id"
                            :value="country"
                        >
                            {{ country.dialCode }} ({{ country.iso2 }}) - {{ country.name }}
                            <ComboboxItemIndicator>
                                <Check :class="cn('ml-auto h-4 w-4')" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxList>
            </Combobox>

            <!-- Phone Number Input -->
            <Input
                :id="name"
                v-model="phoneNumber"
                type="text"
                inputmode="numeric"
                :tabindex="tabindex"
                :placeholder="placeholder"
                maxlength="15"
                @input="onPhoneInput"
            />
        </div>

        <!-- Hidden input for form submission -->
        <input type="hidden" :name="name" :value="phoneJsonValue" />

        <!-- Error message -->
        <InputError :message="error" />
    </div>
</template>
