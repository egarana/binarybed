<script setup lang="ts">
import { ref, watch, computed, type Ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { X, PlusCircle, CalendarDays, Check } from 'lucide-vue-next';
import { Link, type InertiaLinkProps } from '@inertiajs/vue3';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxTrigger,
    ComboboxList,
    ComboboxGroup,
    ComboboxItem,
    ComboboxItemIndicator,
} from '@/components/ui/combobox';
import { parseDate, getLocalTimeZone, type DateValue } from '@internationalized/date';
import dayjs from 'dayjs';
import { cn } from '@/lib/utils';

export interface FilterConfig {
    name: string;           // e.g., 'roles', 'status'
    label: string;          // e.g., 'Roles', 'Status'
    options: string[] | { value: string; label: string }[];  // Available options
    placeholder?: string;   // Placeholder for dropdown label
}

export interface DateOfOption {
    value: string;
    label: string;
}

export interface CalendarConfig {
    enabled?: boolean;              // Enable/disable calendar filters
    dateOfOptions?: DateOfOption[]; // Options for date field selector (e.g., check_in, check_out)
    showDateOfSelector?: boolean;   // Show the "Date of" dropdown
    dateOfLabel?: string;           // Label for date of selector
    fromLabel?: string;             // Label for "from" date button
    toLabel?: string;               // Label for "to" date button
    dateFieldName?: string;         // Field name for date type (default: 'dateOf')
    fromFieldName?: string;         // Field name for from date (default: 'from')
    toFieldName?: string;           // Field name for to date (default: 'until')
    initialFrom?: string;           // Initial from date (YYYY-MM-DD)
    initialTo?: string;             // Initial to date (YYYY-MM-DD)
    initialDateOf?: string;         // Initial date of value
}

interface Props {
    searchPlaceholder?: string;
    showSearch?: boolean;
    showAddButton?: boolean;
    addButtonLabel?: string;
    addButtonRoute?: NonNullable<InertiaLinkProps['href']>;
    addButtonBehavior?: 'link' | 'dialog';
    disabled?: boolean;
    refresh?: (params?: Record<string, any>) => void;
    searchField?: string; // Single field name to search, e.g., 'name'
    searchFields?: string[]; // Multiple fields to search, e.g., ['name', 'email', 'id']
    initialSearch?: string;
    filters?: FilterConfig[]; // Multi-select dropdown filters
    calendar?: CalendarConfig; // Calendar date range filters
    dialogOpen?: Ref<boolean>; // External control for dialog open/close state
}

const props = withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    showSearch: true,
    showAddButton: false,
    addButtonLabel: 'Add',
    addButtonBehavior: 'link',
    disabled: false,
});

// Initialize search from URL params
const initSearchFromUrl = (): string => {
    const urlParams = new URLSearchParams(window.location.search);

    // Check for multi-field search
    if (props.searchFields && props.searchFields.length > 0) {
        return urlParams.get('search') || '';
    }

    // Check for single field search
    if (props.searchField) {
        return urlParams.get(`filter[${props.searchField}]`) || '';
    }

    // Default to 'name' field
    return urlParams.get('filter[name]') || '';
};

const search = ref(props.initialSearch || initSearchFromUrl());

// Track selected filter values
const selectedFilters = ref<Record<string, string[]>>({});

// Computed property for dialog open state
const dialogOpenComputed = computed(() => props.dialogOpen?.value ?? false);

// Handler for dialog open state updates
const handleDialogOpenUpdate = (val: boolean) => {
    if (props.dialogOpen) {
        // eslint-disable-next-line vue/no-mutating-props
        props.dialogOpen.value = val;
    }
};

// ===== Calendar State Management =====
const calendarEnabled = computed(() => props.calendar?.enabled ?? false);

// Date of selector state
const dateOf = ref<DateOfOption | undefined>(
    props.calendar?.dateOfOptions && props.calendar?.initialDateOf
        ? props.calendar.dateOfOptions.find(opt => opt.value === props.calendar?.initialDateOf)
        : props.calendar?.dateOfOptions?.[0]
);

// From and To date states
const fromDate = ref<DateValue | undefined>(
    props.calendar?.initialFrom ? parseDate(props.calendar.initialFrom) : undefined
);

const toDate = ref<DateValue | undefined>(
    props.calendar?.initialTo ? parseDate(props.calendar.initialTo) : undefined
);

// Popover open states
const openFrom = ref(false);
const openTo = ref(false);

// Computed properties with explicit typing for Calendar v-model
const fromDateComputed = computed({
    get: () => fromDate.value as DateValue | undefined,
    set: (val) => { fromDate.value = val as DateValue | undefined; }
});

const toDateComputed = computed({
    get: () => toDate.value as DateValue | undefined,
    set: (val) => { toDate.value = val as DateValue | undefined; }
});

// Format date for API
const formatDate = (date: DateValue | undefined): string | null => {
    return date ? dayjs(date.toDate(getLocalTimeZone())).format('YYYY-MM-DD') : null;
};

// Handle From Date selection
const handleFromSelect = (date: DateValue | undefined) => {
    if (!date) return;
    const dateValue = date as DateValue;
    fromDate.value = dateValue;
    openFrom.value = false;

    if (!toDate.value) {
        // If toDate not set yet → set toDate = fromDate + 1 day
        toDate.value = dateValue.add({ days: 1 });
    } else if (dateValue.compare(toDate.value as DateValue) > 0) {
        // If fromDate > toDate → shift toDate = fromDate + 1 day
        toDate.value = dateValue.add({ days: 1 });
    }
};

// Handle To Date selection
const handleToSelect = (date: DateValue | undefined) => {
    if (!date) return;
    const dateValue = date as DateValue;
    toDate.value = dateValue;
    openTo.value = false;

    if (!fromDate.value) {
        // If fromDate not set yet → set fromDate = toDate - 1 day
        fromDate.value = dateValue.subtract({ days: 1 });
    } else if (dateValue.compare(fromDate.value as DateValue) < 0) {
        // If toDate < fromDate → shift fromDate = toDate - 1 day
        fromDate.value = dateValue.subtract({ days: 1 });
    }
};

// ===== End Calendar State Management =====

// Check if option is selected
const isFilterSelected = (filterName: string, option: string) => {
    return selectedFilters.value[filterName]?.includes(option) ?? false;
};

// Clear specific filter
const clearFilter = (filterName: string) => {
    selectedFilters.value[filterName] = [];
    triggerRefresh();
};

// Get filter option label
const getOptionLabel = (option: string | { value: string; label: string }) => {
    return typeof option === 'string' ? option : option.label;
};

// Get filter option value
const getOptionValue = (option: string | { value: string; label: string }) => {
    return typeof option === 'string' ? option : option.value;
};

// Initialize filters from URL params
const initFiltersFromUrl = () => {
    if (!props.filters) return;

    const urlParams = new URLSearchParams(window.location.search);
    props.filters.forEach(filter => {
        const paramValue = urlParams.get(filter.name);
        if (paramValue) {
            // Split comma-separated values
            const values = paramValue.split(',').map(v => v.trim());
            
            // Get all valid option values for this filter
            const validOptions = filter.options.map(opt => getOptionValue(opt));
            
            // Filter out invalid values (protection against URL manipulation)
            const validValues = values.filter(v => validOptions.includes(v));
            
            // Only set values if we have valid ones
            selectedFilters.value[filter.name] = validValues;
        } else {
            selectedFilters.value[filter.name] = [];
        }
    });
};

initFiltersFromUrl();

// Initialize calendar filters from URL params
const initCalendarFromUrl = () => {
    if (!calendarEnabled.value || !props.calendar) return;

    const urlParams = new URLSearchParams(window.location.search);

    // Get field names (use defaults if not specified)
    const dateFieldName = props.calendar.dateFieldName || 'dateOf';
    const fromFieldName = props.calendar.fromFieldName || 'from';
    const toFieldName = props.calendar.toFieldName || 'until';

    // Read dateOf from URL
    if (props.calendar.showDateOfSelector && props.calendar.dateOfOptions) {
        const dateOfParam = urlParams.get(dateFieldName);
        if (dateOfParam) {
            const foundOption = props.calendar.dateOfOptions.find(opt => opt.value === dateOfParam);
            if (foundOption) {
                dateOf.value = foundOption;
            }
        }
    }

    // Read from date from URL
    const fromParam = urlParams.get(fromFieldName);
    if (fromParam) {
        try {
            fromDate.value = parseDate(fromParam);
        } catch (e) {
            console.error('Failed to parse from date:', e);
        }
    }

    // Read to date from URL
    const toParam = urlParams.get(toFieldName);
    if (toParam) {
        try {
            toDate.value = parseDate(toParam);
        } catch (e) {
            console.error('Failed to parse to date:', e);
        }
    }
};

initCalendarFromUrl();

// Toggle filter option
const toggleFilterOption = (filterName: string, option: string) => {
    if (!selectedFilters.value[filterName]) {
        selectedFilters.value[filterName] = [];
    }

    const index = selectedFilters.value[filterName].indexOf(option);
    if (index > -1) {
        selectedFilters.value[filterName].splice(index, 1);
    } else {
        selectedFilters.value[filterName].push(option);
    }

    triggerRefresh();
};

// Build all filter params
const buildFilterParams = () => {
    const filterParams: Record<string, any> = {};

    // Add search params
    if (search.value) {
        if (props.searchFields && props.searchFields.length > 0) {
            filterParams['search'] = search.value;
            filterParams['fields'] = props.searchFields.join(',');
        } else if (props.searchField) {
            filterParams[`filter[${props.searchField}]`] = search.value;
        } else {
            filterParams['filter[name]'] = search.value;
        }
    } else {
        if (props.searchFields && props.searchFields.length > 0) {
            filterParams['search'] = undefined;
            filterParams['fields'] = undefined;
        } else if (props.searchField) {
            filterParams[`filter[${props.searchField}]`] = undefined;
        } else {
            filterParams['filter[name]'] = undefined;
        }
    }

    // Add dropdown filter params
    if (props.filters) {
        props.filters.forEach(filter => {
            const selected = selectedFilters.value[filter.name] || [];
            if (selected.length > 0) {
                filterParams[filter.name] = selected.join(',');
            } else {
                filterParams[filter.name] = undefined;
            }
        });
    }

    // Add calendar filter params
    if (calendarEnabled.value && props.calendar) {
        const dateFieldName = props.calendar.dateFieldName || 'dateOf';
        const fromFieldName = props.calendar.fromFieldName || 'from';
        const toFieldName = props.calendar.toFieldName || 'until';

        // Add date of field
        if (props.calendar.showDateOfSelector && dateOf.value) {
            filterParams[dateFieldName] = dateOf.value.value;
        }

        // Add from date
        filterParams[fromFieldName] = formatDate(fromDate.value as DateValue | undefined);

        // Add to date
        filterParams[toFieldName] = formatDate(toDate.value as DateValue | undefined);
    }

    return filterParams;
};

// Trigger refresh with all filters
const triggerRefresh = () => {
    if (!props.refresh) return;
    const filterParams = buildFilterParams();
    props.refresh({ ...filterParams, page: 1 });
};

// Watch search input and calendar values
watch(search, () => {
    triggerRefresh();
});

watch([dateOf, fromDate, toDate], () => {
    if (calendarEnabled.value) {
        triggerRefresh();
    }
});

// Reset all filters
const resetAllFilters = () => {
    search.value = '';
    if (props.filters) {
        props.filters.forEach(filter => {
            selectedFilters.value[filter.name] = [];
        });
    }
    // Reset calendar values
    if (calendarEnabled.value && props.calendar) {
        dateOf.value = props.calendar.dateOfOptions?.[0];
        fromDate.value = undefined;
        toDate.value = undefined;
    }
    triggerRefresh();
};

// Check if any filter is active
const hasActiveFilters = computed(() => {
    if (search.value) return true;
    if (props.filters) {
        const hasDropdownFilters = props.filters.some(filter =>
            (selectedFilters.value[filter.name]?.length ?? 0) > 0
        );
        if (hasDropdownFilters) return true;
    }
    // Check calendar filters
    if (calendarEnabled.value) {
        if (fromDate.value || toDate.value) return true;
    }
    return false;
});
</script>

<template>
    <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
        <!-- Search Input -->
        <Input
            v-if="showSearch"
            v-model="search"
            id="search"
            :placeholder="searchPlaceholder"
            :disabled="disabled"
            class="text-sm md:max-w-[250px]"
        />

        <!-- Dropdown Filters -->
        <template v-if="filters">
            <DropdownMenu v-for="filter in filters" :key="filter.name">
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
                        <PlusCircle class="w-4 h-4" />
                        <span>{{ filter.label }}</span>
                        <div
                            v-if="selectedFilters[filter.name]?.length > 0"
                            class="flex items-center gap-1 border-s ps-3.5 ms-1.5"
                        >
                            <template v-if="selectedFilters[filter.name].filter(v => filter.options.some(o => getOptionValue(o) === v)).length < 3">
                                <Badge
                                    v-for="value in selectedFilters[filter.name].filter(v => filter.options.some(o => getOptionValue(o) === v))"
                                    :key="value"
                                    variant="secondary"
                                    class="font-normal rounded-sm px-1.5"
                                >
                                    {{ getOptionLabel(filter.options.find(o => getOptionValue(o) === value) || value) }}
                                </Badge>
                            </template>
                            <Badge
                                v-else
                                variant="secondary"
                                class="font-normal rounded-sm px-1"
                            >
                                {{ selectedFilters[filter.name].filter(v => filter.options.some(o => getOptionValue(o) === v)).length }} selected
                            </Badge>
                        </div>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-[200px]">
                    <DropdownMenuLabel>{{ filter.placeholder || filter.label }}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup class="max-h-[300px] overflow-y-auto">
                        <DropdownMenuCheckboxItem
                            v-for="option in filter.options"
                            :key="getOptionValue(option)"
                            :model-value="isFilterSelected(filter.name, getOptionValue(option))"
                            @select.prevent="toggleFilterOption(filter.name, getOptionValue(option))"
                            class="dropdown-filter-item"
                        >
                            {{ getOptionLabel(option) }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuGroup>
                    <template v-if="selectedFilters[filter.name]?.length > 0">
                        <div class="border-t -mx-1 mb-1 my-1"></div>
                        <DropdownMenuGroup class="p-0">
                            <Button
                                variant="ghost"
                                class="w-full font-normal text-sm h-auto py-1.5 justify-center"
                                @click="clearFilter(filter.name)"
                            >
                                Clear filter
                            </Button>
                        </DropdownMenuGroup>
                    </template>
                </DropdownMenuContent>
            </DropdownMenu>
        </template>

        <!-- Calendar Filters -->
        <template v-if="calendarEnabled && calendar">
            <!-- Date Of Selector -->
            <Combobox v-if="calendar.showDateOfSelector && calendar.dateOfOptions" v-model="dateOf">
                <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button variant="outline" class="border-dashed font-normal w-full md:w-auto" :disabled="disabled">
                            <CalendarDays class="stroke-[1.8]" />
                            <span>{{ calendar.dateOfLabel || 'Date of' }}</span>
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
                            v-for="(item, index) in calendar.dateOfOptions"
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

            <!-- From Date -->
            <Popover v-model:open="openFrom">
                <PopoverTrigger as-child>
                    <Button variant="outline" class="border-dashed font-normal w-full md:w-auto" :disabled="disabled">
                        <PlusCircle />
                        <span>{{ calendar.fromLabel || 'From' }}</span>
                        <div v-if="fromDate" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
                            <Badge variant="secondary" class="font-normal rounded-sm px-1.5">
                                {{ dayjs(fromDate.toDate(getLocalTimeZone())).format('MMM DD, YYYY') }}
                            </Badge>
                        </div>
                    </Button>
                </PopoverTrigger>
                <PopoverContent align="start" class="w-auto p-0">
                    <!-- @ts-expect-error Type inference issue with Calendar v-model -->
                    <Calendar
                        v-model="fromDateComputed"
                        initial-focus
                        @update:model-value="handleFromSelect"
                    />
                </PopoverContent>
            </Popover>

            <!-- To Date -->
            <Popover v-model:open="openTo">
                <PopoverTrigger as-child>
                    <Button variant="outline" class="border-dashed font-normal w-full md:w-auto" :disabled="disabled">
                        <PlusCircle />
                        <span>{{ calendar.toLabel || 'Until' }}</span>
                        <div v-if="toDate" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
                            <Badge variant="secondary" class="font-normal rounded-sm px-1.5">
                                {{ dayjs(toDate.toDate(getLocalTimeZone())).format('MMM DD, YYYY') }}
                            </Badge>
                        </div>
                    </Button>
                </PopoverTrigger>
                <PopoverContent align="start" class="w-auto p-0">
                    <!-- @ts-expect-error Type inference issue with Calendar v-model -->
                    <Calendar
                        v-model="toDateComputed"
                        initial-focus
                        @update:model-value="handleToSelect"
                    />
                </PopoverContent>
            </Popover>
        </template>

        <!-- Reset Filter Button -->
        <Button
            v-if="hasActiveFilters"
            variant="ghost"
            @click="resetAllFilters"
            :disabled="disabled"
            class="w-full border md:border-0 md:w-auto"
        >
            Reset
            <X />
        </Button>

        <!-- Add Button -->
        <template v-if="showAddButton">
            <Link
                v-if="addButtonBehavior === 'link' && addButtonRoute"
                :href="addButtonRoute"
                class="ms-auto"
            >
                <Button class="w-full md:w-auto" :disabled="disabled">
                    <PlusCircle /> {{ addButtonLabel }}
                </Button>
            </Link>

            <Dialog 
                v-else-if="addButtonBehavior === 'dialog'" 
                :open="dialogOpenComputed"
                @update:open="handleDialogOpenUpdate"
            >
                <DialogTrigger as-child>
                    <Button class="w-full md:w-auto ms-auto" :disabled="disabled">
                        <PlusCircle /> {{ addButtonLabel }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <slot name="dialog-content" />
                </DialogContent>
            </Dialog>
        </template>
    </div>
</template>