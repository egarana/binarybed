<script setup lang="ts">
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { X, PlusCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Props {
    searchPlaceholder?: string;
    showSearch?: boolean;
    showAddButton?: boolean;
    addButtonLabel?: string;
    addButtonRoute?: string;
    disabled?: boolean;
    refresh?: (params?: Record<string, any>) => void;
    searchField?: string; // Field name to search, e.g., 'name'
    initialSearch?: string;
}

const props = withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    showSearch: true,
    showAddButton: false,
    addButtonLabel: 'Add',
    disabled: false,
    searchField: 'name', // Default search by 'name'
});

const search = ref(props.initialSearch || '');

// Watch search input and trigger refresh with filter params
watch(search, (value) => {
    if (!props.refresh) return;

    // Build filter object for Spatie QueryBuilder
    // Example: filter[name]=keyword
    const filterParams: Record<string, any> = {};

    if (value) {
        filterParams[`filter[${props.searchField}]`] = value;
    } else {
        // Reset filter when search is empty
        filterParams[`filter[${props.searchField}]`] = undefined;
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
