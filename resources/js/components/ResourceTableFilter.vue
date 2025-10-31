<script setup lang="ts">
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { X, PlusCircle } from 'lucide-vue-next';
import { Link, type InertiaLinkProps } from '@inertiajs/vue3';

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
}

const props = withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    showSearch: true,
    showAddButton: false,
    addButtonLabel: 'Add',
    disabled: false,
});

const search = ref(props.initialSearch || '');

// Watch search input and trigger refresh with filter params
watch(search, (value) => {
    if (!props.refresh) return;

    const filterParams: Record<string, any> = {};

    if (value) {
        // If searchFields (multiple) is provided, use simple 'search' param
        // Backend will handle OR condition across multiple fields
        if (props.searchFields && props.searchFields.length > 0) {
            filterParams['search'] = value;
            // Send fields as single param with comma separator (simplest approach)
            filterParams['fields'] = props.searchFields.join(',');
        }
        // Otherwise use single searchField with filter[] format
        else if (props.searchField) {
            filterParams[`filter[${props.searchField}]`] = value;
        }
        // Fallback to 'name' if nothing specified
        else {
            filterParams['filter[name]'] = value;
        }
    } else {
        // Reset filters when search is empty
        if (props.searchFields && props.searchFields.length > 0) {
            filterParams['search'] = undefined;
            filterParams['fields'] = undefined;
        } else if (props.searchField) {
            filterParams[`filter[${props.searchField}]`] = undefined;
        } else {
            filterParams['filter[name]'] = undefined;
        }
    }

    props.refresh({ ...filterParams, page: 1 });
});

const resetFilters = () => {
    search.value = '';
};
</script>

<template>
    <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
        <Input
            v-if="showSearch"
            v-model="search"
            id="search"
            :placeholder="searchPlaceholder"
            :disabled="disabled"
            class="text-sm md:max-w-[250px]"
        />
        <Button
            v-if="showSearch && search !== ''"
            variant="ghost"
            @click="resetFilters"
            :disabled="disabled || search === ''"
            class="w-full border md:border-0 md:w-auto"
        >
            Reset
            <X />
        </Button>
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
