<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { X, PlusCircle } from 'lucide-vue-next';
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

export interface FilterConfig {
    name: string;           // e.g., 'roles', 'status'
    label: string;          // e.g., 'Roles', 'Status'
    options: string[] | { value: string; label: string }[];  // Available options
    placeholder?: string;   // Placeholder for dropdown label
}

interface Props {
    searchPlaceholder?: string;
    showSearch?: boolean;
    showAddButton?: boolean;
    addButtonLabel?: string;
    addButtonRoute?: NonNullable<InertiaLinkProps['href']>;
    disabled?: boolean;
    refresh?: (params?: Record<string, any>) => void;
    searchField?: string; // Single field name to search, e.g., 'name'
    searchFields?: string[]; // Multiple fields to search, e.g., ['name', 'email', 'id']
    initialSearch?: string;
    filters?: FilterConfig[]; // Multi-select dropdown filters
}

const props = withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    showSearch: true,
    showAddButton: false,
    addButtonLabel: 'Add',
    disabled: false,
});

const search = ref(props.initialSearch || '');

// Track selected filter values
const selectedFilters = ref<Record<string, string[]>>({});

// Initialize filters from URL params
const initFiltersFromUrl = () => {
    if (!props.filters) return;

    const urlParams = new URLSearchParams(window.location.search);
    props.filters.forEach(filter => {
        const paramValue = urlParams.get(filter.name);
        if (paramValue) {
            selectedFilters.value[filter.name] = paramValue.split(',');
        } else {
            selectedFilters.value[filter.name] = [];
        }
    });
};

initFiltersFromUrl();

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

    return filterParams;
};

// Trigger refresh with all filters
const triggerRefresh = () => {
    if (!props.refresh) return;
    const filterParams = buildFilterParams();
    props.refresh({ ...filterParams, page: 1 });
};

// Watch search input
watch(search, () => {
    triggerRefresh();
});

// Reset all filters
const resetAllFilters = () => {
    search.value = '';
    if (props.filters) {
        props.filters.forEach(filter => {
            selectedFilters.value[filter.name] = [];
        });
    }
    triggerRefresh();
};

// Check if any filter is active
const hasActiveFilters = computed(() => {
    if (search.value) return true;
    if (props.filters) {
        return props.filters.some(filter =>
            (selectedFilters.value[filter.name]?.length ?? 0) > 0
        );
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
                            <template v-if="selectedFilters[filter.name].length < 3">
                                <Badge
                                    v-for="value in selectedFilters[filter.name]"
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
                                {{ selectedFilters[filter.name].length }} selected
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
                        >
                            {{ getOptionLabel(option) }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuGroup>
                    <template v-if="selectedFilters[filter.name]?.length > 0">
                        <DropdownMenuSeparator />
                        <DropdownMenuGroup class="p-0">
                            <Button
                                variant="ghost"
                                class="w-full font-normal text-sm h-auto py-1.5 justify-start"
                                @click="clearFilter(filter.name)"
                            >
                                Clear filter
                            </Button>
                        </DropdownMenuGroup>
                    </template>
                </DropdownMenuContent>
            </DropdownMenu>
        </template>

        <!-- Reset All Button -->
        <Button
            v-if="hasActiveFilters"
            variant="ghost"
            @click="resetAllFilters"
            :disabled="disabled"
            class="w-full border md:border-0 md:w-auto"
        >
            Reset All
            <X />
        </Button>

        <!-- Add Button -->
        <Link
            v-if="showAddButton && addButtonRoute"
            :href="addButtonRoute"
            class="ms-auto"
        >
            <Button class="w-full md:w-auto" :disabled="disabled">
                <PlusCircle /> {{ addButtonLabel }}
            </Button>
        </Link>
    </div>
</template>