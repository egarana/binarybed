<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { ChevronsUpDown, ChevronsLeft, ChevronLeft, ChevronRight, ChevronsRight, EllipsisVertical } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import EditAssignmentDialog from '@/components/EditAssignmentDialog.vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);
import { computed, ref, watch } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{
    data: {
        data?: any[];
        from?: number;
        to?: number;
        total?: number;
        current_page?: number;
        last_page?: number;
        prev_page_url?: string | null;
        next_page_url?: string | null;
        first_page_url?: string | null;
        last_page_url?: string | null;
        per_page?: number;
    };
    columns: { key: string; label: string; sortable?: boolean; className?: string; headClassName?: string; formatter?: (value: any, item: any) => string }[];
    sortState?: { field: string; direction: 'asc' | 'desc' };
    handleSort?: (field: string) => void;
    refresh?: (params?: Record<string, any>, immediate?: boolean) => void;
    editRoute?: (item: any) => string;
    deleteRoute?: (item: any) => { url: string; data?: Record<string, any> } | null;
    resourceName?: string;
    itemKey?: string | ((item: any) => string | number);
    customActions?: Array<{
        icon: any; // Lucide icon component
        tooltip: string;
        url: (item: any) => string;
        variant?: 'ghost' | 'outline' | 'default' | 'secondary' | 'destructive';
        condition?: (item: any) => boolean; // Optional condition to show/hide button
    }>;
    editAssignmentConfig?: {
        getEditUrl: (item: any) => string;
        getCurrentRole: (item: any) => string;
        roleOptions: Array<{ value: string; label: string }>;
        entityName?: string;
        userDisplayField?: string;
        roleFieldName?: string;
        title?: string;
        description?: string;
        tooltip?: string;
        icon?: any;
        submitButtonLabel?: string;
    };
    deleteIcon?: any;
    deleteActionLabel?: string;
    deleteTitle?: string;
    deleteDescription?: string;
    deleteConfirmLabel?: string;
}>();

const items = computed(() => props.data?.data ?? []);

// Function untuk mendapatkan unique key dari item
const getItemKey = (item: any): string | number => {
    if (!props.itemKey) {
        return item.id;
    }

    if (typeof props.itemKey === 'function') {
        return props.itemKey(item);
    }

    return item[props.itemKey];
};

const pagination = computed(() => {
    const meta = props.data ?? {};

    return {
        current_page: meta.current_page ?? 1,
        first_page_url: meta.first_page_url ?? null,
        prev_page_url: meta.prev_page_url ?? null,
        next_page_url: meta.next_page_url ?? null,
        last_page: meta.last_page ?? 1,
        last_page_url: meta.last_page_url ?? null,
        from: meta.from ?? 0,
        to: meta.to ?? 0,
        total: meta.total ?? 0,
        per_page: meta.per_page ?? 15,
    };
});

const isFirstPage = computed(() => pagination.value.current_page <= 1);
const isLastPage = computed(() => pagination.value.current_page >= pagination.value.last_page);

const perPageOptions = [10, 15, 20, 30, 40, 50];

const incoming = Number(pagination.value.per_page ?? 10);

// snap ke opsi terdekat yang <= incoming
const snapped = perPageOptions.reduce((acc, v) =>
    incoming >= v ? v : acc,
    perPageOptions[0]
);

const selectedPerPage = ref(String(snapped));

watch(selectedPerPage, (v) => {
    if (props.refresh) {
        props.refresh({ per_page: v, page: 1 });
    }
});

// Handle delete dengan logic untuk pindah ke page sebelumnya jika item terakhir di last page
const handleDelete = () => {
    if (!props.refresh) return;
    // Cek apakah kita di last page dan item tinggal 1
    const isLastPage = pagination.value.current_page === pagination.value.last_page;
    const hasOnlyOneItem = items.value.length === 1;
    if (isLastPage && hasOnlyOneItem && pagination.value.current_page > 1) {
         // Pindah ke page sebelumnya dengan immediate refresh
        props.refresh({ page: pagination.value.current_page - 1 }, true);
    } else {
        // Refresh biasa dengan immediate refresh
        props.refresh({}, true);
    }
};
</script>

<template>
    <div class="overflow-hidden rounded-lg border">
        <div class="relative w-full overflow-x-auto [&>div]:scrollbar-hide">
            <Table>
                <TableHeader>
                    <TableRow class="bg-muted">
                        <TableHead
                            v-for="col in columns"
                            :key="col.key"
                            class="whitespace-nowrap"
                            :class="col.headClassName"
                        >
                            <Button
                                v-if="col.sortable && handleSort"
                                variant="ghost"
                                size="sm"
                                class="flex items-center gap-1.5 ms-1"
                                @click="handleSort(col.key)"
                            >
                                {{ col.label }}
                                <ChevronsUpDown class="!w-3 !h-3" />
                            </Button>

                            <span
                                v-else
                                class="text-sm font-medium"
                            >
                                {{ col.label }}
                            </span>
                        </TableHead>

                        <TableHead class="w-[88px]"></TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="items.length">
                        <TableRow v-for="item in items" :key="getItemKey(item)">
                            <!-- Custom slot untuk debug/custom rendering -->
                            <template v-if="$slots.item">
                                <TableCell :colspan="columns.length" class="ps-5">
                                    <slot name="item" :item="item"></slot>
                                </TableCell>
                                <TableCell class="text-right flex items-center">
                                    <!-- Edit Assignment Dialog -->
                                    <template v-if="editAssignmentConfig">
                                        <EditAssignmentDialog
                                            :edit-url="editAssignmentConfig.getEditUrl(item)"
                                            :item="item"
                                            :current-role="editAssignmentConfig.getCurrentRole(item)"
                                            :role-options="editAssignmentConfig.roleOptions"
                                            :entity-name="editAssignmentConfig.entityName"
                                            :user-display-field="editAssignmentConfig.userDisplayField"
                                            :role-field-name="editAssignmentConfig.roleFieldName"
                                            :title="editAssignmentConfig.title"
                                            :description="editAssignmentConfig.description"
                                            :tooltip="editAssignmentConfig.tooltip"
                                            :icon="editAssignmentConfig.icon"
                                            :submit-button-label="editAssignmentConfig.submitButtonLabel"
                                            @updated="handleDelete"
                                        />
                                    </template>

                                    <template v-if="deleteRoute && deleteRoute(item)">
                                        <ConfirmDeleteDialog
                                            :delete-url="deleteRoute(item)!.url"
                                            :delete-data="deleteRoute(item)!.data"
                                            :entity-name="resourceName || 'item'"
                                            :icon="deleteIcon"
                                            :tooltip="deleteActionLabel"
                                            :title="deleteTitle"
                                            :description="deleteDescription"
                                            :confirm-text="deleteConfirmLabel"
                                            @deleted="handleDelete"
                                        />
                                    </template>

                                    <DropdownMenu v-if="editRoute || customActions">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="ms-auto">
                                                <EllipsisVertical class="w-4 h-4 text-muted-foreground" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                            <DropdownMenuItem v-if="editRoute" as-child>
                                                <Link :href="editRoute(item)">
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <!-- Custom Actions -->
                                            <template v-if="customActions">
                                                <DropdownMenuSeparator />
                                                <template v-for="(action, index) in customActions" :key="index">
                                                    <DropdownMenuItem v-if="!action.condition || action.condition(item)" as-child>
                                                        <Link :href="action.url(item)">
                                                            {{ action.tooltip }}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                </template>
                                            </template>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </template>

                            <!-- Default rendering -->
                            <template v-else>
                                <TableCell
                                    v-for="col in columns"
                                    :key="col.key"
                                    class="truncate ps-5"
                                    :class="col.className"
                                >
                                    <slot
                                        :name="`cell-${col.key}`"
                                        :item="item"
                                        :value="item[col.key]"
                                    >
                                        <template v-if="col.formatter">
                                            {{ col.formatter(item[col.key], item) }}
                                        </template>
                                        <template v-else-if="['created_at', 'updated_at', 'assigned_at'].includes(col.key)">
                                            {{ dayjs(item[col.key]).fromNow() }}
                                        </template>
                                        <template v-else>
                                            {{ item[col.key] }}
                                        </template>
                                    </slot>
                                </TableCell>

                                <TableCell class="text-right flex items-center gap-0">
                                    <!-- Edit Assignment Dialog -->
                                    <template v-if="editAssignmentConfig">
                                        <EditAssignmentDialog
                                            :edit-url="editAssignmentConfig.getEditUrl(item)"
                                            :item="item"
                                            :current-role="editAssignmentConfig.getCurrentRole(item)"
                                            :role-options="editAssignmentConfig.roleOptions"
                                            :entity-name="editAssignmentConfig.entityName"
                                            :user-display-field="editAssignmentConfig.userDisplayField"
                                            :role-field-name="editAssignmentConfig.roleFieldName"
                                            :title="editAssignmentConfig.title"
                                            :description="editAssignmentConfig.description"
                                            :tooltip="editAssignmentConfig.tooltip"
                                            :icon="editAssignmentConfig.icon"
                                            :submit-button-label="editAssignmentConfig.submitButtonLabel"
                                            @updated="handleDelete"
                                        />
                                    </template>

                                    <template v-if="deleteRoute && deleteRoute(item)">
                                        <ConfirmDeleteDialog
                                            :delete-url="deleteRoute(item)!.url"
                                            :delete-data="deleteRoute(item)!.data"
                                            :entity-name="resourceName || 'item'"
                                            :icon="deleteIcon"
                                            :tooltip="deleteActionLabel"
                                            :title="deleteTitle"
                                            :description="deleteDescription"
                                            :confirm-text="deleteConfirmLabel"
                                            @deleted="handleDelete"
                                        />
                                    </template>

                                    <DropdownMenu v-if="editRoute || customActions">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="ms-auto">
                                                <EllipsisVertical class="w-4 h-4 text-muted-foreground" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                            <DropdownMenuItem v-if="editRoute" as-child>
                                                <Link :href="editRoute(item)">
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <!-- Custom Actions -->
                                            <template v-if="customActions">
                                                <DropdownMenuSeparator />
                                                <template v-for="(action, index) in customActions" :key="index">
                                                    <DropdownMenuItem v-if="!action.condition || action.condition(item)" as-child>
                                                        <Link :href="action.url(item)">
                                                            {{ action.tooltip }}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                </template>
                                            </template>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </template>
                        </TableRow>
                    </template>

                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="columns.length + 1"
                                class="text-center text-muted-foreground py-4"
                            >
                                No {{ resourceName || 'item' }} found.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
    </div>

    <!-- Pagination info -->
    <div
        v-if="pagination.total > 0"
        class="px-2 mt-auto pt-1 flex flex-wrap items-center md:justify-end md:gap-4 lg:gap-10"
    >
        <div class="basis-full text-sm text-muted-foreground shrink-0 mb-4 md:mb-0 md:basis-auto md:me-auto md:order-1">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} records.
        </div>
        <div class="basis-1/3 text-sm font-medium shrink-0 md:basis-auto md:order-3">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </div>
        <div class="basis-2/3 flex items-center justify-end gap-2 md:basis-auto md:order-4">
            <!-- First page -->
            <Link :href="pagination.first_page_url ?? undefined" v-if="!isFirstPage">
                <Button variant="ghost" size="icon">
                    <ChevronsLeft class="w-4 h-4" />
                </Button>
            </Link>
            <Button v-else variant="ghost" size="icon" disabled class="bg-muted">
                <ChevronsLeft class="w-4 h-4 text-muted-foreground/50" />
            </Button>

            <!-- Prev -->
            <Link :href="pagination.prev_page_url ?? undefined" v-if="pagination.prev_page_url">
                <Button variant="ghost" size="icon">
                    <ChevronLeft class="w-4 h-4" />
                </Button>
            </Link>
            <Button v-else variant="ghost" size="icon" disabled class="bg-muted">
                <ChevronLeft class="w-4 h-4 text-muted-foreground/50" />
            </Button>

            <!-- Next -->
            <Link :href="pagination.next_page_url ?? undefined" v-if="pagination.next_page_url">
                <Button variant="ghost" size="icon">
                    <ChevronRight class="w-4 h-4" />
                </Button>
            </Link>
            <Button v-else variant="ghost" size="icon" disabled class="bg-muted">
                <ChevronRight class="w-4 h-4 text-muted-foreground/50" />
            </Button>

            <!-- Last page -->
            <Link :href="pagination.last_page_url ?? undefined" v-if="!isLastPage">
                <Button variant="ghost" size="icon">
                    <ChevronsRight class="w-4 h-4" />
                </Button>
            </Link>
            <Button v-else variant="ghost" size="icon" disabled class="bg-muted">
                <ChevronsRight class="w-4 h-4 text-muted-foreground/50" />
            </Button>
        </div>
        <div class="basis-full text-sm font-medium flex items-center justify-between gap-2 shrink-0 mt-4 md:mt-0 md:basis-auto md:order-2">
            <span>Rows per page</span>
            <Select v-model="selectedPerPage">
                <SelectTrigger class="w-[70px]">
                    <SelectValue :value="selectedPerPage" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="v in perPageOptions" :key="v" :value="String(v)">
                        {{ v }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>