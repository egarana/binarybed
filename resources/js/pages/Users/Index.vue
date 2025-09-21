<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Select from '@/components/ui/select/Select.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import Table from '@/components/ui/table/Table.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import Tooltip from '@/components/ui/tooltip/Tooltip.vue';
import TooltipContent from '@/components/ui/tooltip/TooltipContent.vue';
import TooltipProvider from '@/components/ui/tooltip/TooltipProvider.vue';
import TooltipTrigger from '@/components/ui/tooltip/TooltipTrigger.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Check, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ChevronsUpDown, Pencil, PlusCircle, Trash2, X } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogClose from '@/components/ui/dialog/DialogClose.vue';
import { toast } from 'vue-sonner';
import Badge from '@/components/ui/badge/Badge.vue';
import Popover from '@/components/ui/popover/Popover.vue';
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue';
import PopoverContent from '@/components/ui/popover/PopoverContent.vue';
import Command from '@/components/ui/command/Command.vue';
import CommandInput from '@/components/ui/command/CommandInput.vue';
import CommandList from '@/components/ui/command/CommandList.vue';
import CommandEmpty from '@/components/ui/command/CommandEmpty.vue';
import CommandGroup from '@/components/ui/command/CommandGroup.vue';
import CommandItem from '@/components/ui/command/CommandItem.vue';

const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

dayjs.extend(relativeTime);

const page = usePage()
const users = ref(page.props.users)
const perPage = ref(String(page.props.perPage || 20))
const search = ref(page.props.search || '')
const sort = ref(page.props.sort || '') // current sort
const sortField = ref('name') // default field
const isLoading = ref(true)

const availableRoles = ref(page.props.roles)
const selectedRoles = ref<string[]>([]);

const isSelected = (role: string) => selectedRoles.value.includes(role);

const dialogStates = reactive<Record<number, boolean>>({});

const form = useForm({
    _method: 'DELETE',
});

const deleteItem = (e: Event, id: number) => {
    e.preventDefault();

    form.delete(route('users.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal(id);

            toast('User deleted', {
                description: 'The user has been deleted successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: (errors) => {
            if (errors.authorization) {
                toast('Forbidden', {
                    description: errors.authorization,
                    action: {
                        label: 'Close',
                    },
                });
            }
        },
        onFinish: () => {
            fetchData()
            form.reset()
        },
    });
};

const closeModal = (itemId: number) => {
    form.clearErrors();
    form.reset();
    dialogStates[itemId] = false;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const fetchData = () => {
    isLoading.value = true

    router.get(route('users.index'), {
        search: search.value,
        perPage: perPage.value,
        sort: sort.value,
        page: users.value.current_page,
        selectedRoles: selectedRoles.value.join(',')
        // selectedRoles: selectedRoles.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['users'],
        onSuccess: () => {
            users.value = usePage().props.users
        },
        onFinish: () => {
            isLoading.value = false // stop loading
        }
    })
}

onMounted(() => {
    isLoading.value = false
    users.value.data.forEach(user => {
        if (!(user.id in dialogStates)) {
            dialogStates[user.id] = false;
        }
    });
})

const handleSort = (field: string) => {
    if (sortField.value === field) {
        sort.value = sort.value === field ? `-${field}` : field
    } else {
        sortField.value = field
        sort.value = field
    }
    fetchData()
}

watch([search, selectedRoles, perPage], () => {
    users.value.current_page = 1;
    fetchData();
}, { deep: true });

// Toggle role selection for filter
const toggleRole = (role: string) => {
    if (selectedRoles.value.includes(role)) {
        selectedRoles.value = selectedRoles.value.filter(r => r !== role);
    } else {
        selectedRoles.value.push(role);
    }
};

const clearRolesFilter = () => {
    selectedRoles.value = [];
};

const resetFilters = () => {
    selectedRoles.value = [];
    search.value = '';
};

const isFirstPage = computed(() => users.value.current_page === 1)
const isLastPage = computed(() => users.value.current_page === users.value.last_page)
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
                <Input
                    v-model="search"
                    id="search"
                    placeholder="Search users..."
                    class="text-sm md:max-w-[250px]"
                />
                <Popover>
                    <PopoverTrigger as-child>
                        <Button variant="outline" class="border-dashed font-normal w-full md:w-auto">
                            <PlusCircle/> 
                            <span>Roles</span>
                            <div v-if="selectedRoles.length > 0" class="flex items-center gap-1 border-s ps-3.5 ms-1.5">
                                <Badge v-if="selectedRoles.length < 3" v-for="role in selectedRoles" variant="secondary" class="font-normal rounded-sm px-1.5">
                                    {{ role }}
                                </Badge>
                                <Badge v-else variant="secondary" class="font-normal rounded-sm px-1">
                                    {{ selectedRoles.length }} selected
                                </Badge>
                            </div>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent :align="'start'" class="w-[200px] p-0">
                        <Command v-model="form.permissions">
                            <CommandInput placeholder="Roles" />
                            <CommandList>
                                <CommandEmpty>No results found.</CommandEmpty>
                                <CommandGroup>
                                    <CommandItem
                                        v-for="role in availableRoles"
                                        :key="role"
                                        :value="role"
                                        @select="() => toggleRole(role)"
                                        class="py-[6px] flex items-center gap-4"
                                    >
                                        <div 
                                            :class="isSelected(role) ? 'bg-foreground' : ''"
                                            class="h-4 w-4 flex items-center justify-center border rounded-sm border-foreground"
                                        >
                                            <Check v-if="isSelected(role)" class="text-background" />
                                        </div>
                                        <span>{{ role }}</span>
                                    </CommandItem>
                                </CommandGroup>
                                <CommandGroup class="p-0">
                                    <div v-if="selectedRoles.length > 0" class="p-1 border-t">
                                        <Button
                                            variant="ghost"
                                            class="w-full font-normal text-sm h-auto py-1.5"
                                            @click="clearRolesFilter"
                                            :disabled="selectedRoles.length === 0"
                                        >
                                            Clear filters
                                        </Button>
                                    </div>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
                <Button
                    v-if="selectedRoles.length > 0 || search !== ''"
                    variant="ghost"
                    @click="resetFilters"
                    :disabled="selectedRoles.length === 0 && search === ''"
                    class="w-full border md:border-0 md:w-auto"
                >
                    Reset
                    <X/>
                </Button>
                <Link v-if="permissions.includes('create-users')" :href="'/users/create'" class="ms-auto">
                    <Button class="w-full md:w-auto">
                        <PlusCircle /> Add User
                    </Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg border">
                <div class="relative w-full overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted">
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="handleSort('name')">
                                        Name
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[300px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('email')">
                                        Email
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[100px]">Roles</TableHead>
                                <TableHead class="w-[140px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('created_at')">
                                        Created At
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-[140px]">
                                    <Button variant="ghost" size="sm" @click="handleSort('updated_at')">
                                        Updated At
                                        <ChevronsUpDown class="!w-3 !h-3" />
                                    </Button>
                                </TableHead>
                                <TableHead v-if="permissions.includes('delete-users') || permissions.includes('edit-users')" class="w-[88px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id" v-if="users.data.length">
                                <TableCell
                                    :class="!permissions.includes('delete-users') && !permissions.includes('edit-users') ? '!py-4' : ''"
                                    class="ps-5"
                                >
                                    {{ user.name }}
                                </TableCell>
                                <TableCell class="ps-5">{{ user.email }}</TableCell>
                                <TableCell>
                                    <template v-if="user.roles.length === 0">
                                        <Badge variant="outline" class="font-normal rounded-sm text-muted-foreground">
                                            No role
                                        </Badge>
                                    </template>

                                    <template v-else-if="user.roles.length === 1">
                                        <Badge variant="secondary" class="font-normal rounded-sm px-1">
                                            {{ user.roles[0].name }}
                                        </Badge>
                                    </template>
                                    
                                    <template v-else>
                                        <Popover>
                                            <PopoverTrigger>
                                                <Badge variant="secondary" class="font-normal rounded-sm hover:cursor-pointer hover:underline hover:bg-foreground/15">
                                                    {{ user.roles.length }} roles
                                                </Badge>
                                            </PopoverTrigger>
                                            <PopoverContent :align="'start'" class="flex flex-wrap items-center gap-1 p-2 max-w-[200px]">
                                                <Badge v-for="role in user.roles" variant="secondary" class="font-normal rounded-sm px-1">
                                                    {{ role.name }}
                                                </Badge>
                                            </PopoverContent>
                                        </Popover>
                                    </template>
                                </TableCell>
                                <TableCell class="ps-5">{{ dayjs(user.created_at).fromNow() }}</TableCell>
                                <TableCell class="ps-5">{{ dayjs(user.updated_at).fromNow() }}</TableCell>
                                <TableCell v-if="permissions.includes('delete-users') || permissions.includes('edit-users')" class="text-right">
                                    <TooltipProvider v-if="permissions.includes('edit-users')">
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Link :href="route('users.edit', user.id)" class="ms-auto">
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil class="w-4 h-4 text-muted-foreground" />
                                                    </Button>
                                                </Link>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Edit User</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <Dialog v-model:open="dialogStates[user.id]" v-if="permissions.includes('delete-users')">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger>
                                                    <DialogTrigger as-child>
                                                        <Button variant="ghost" size="icon">
                                                            <Trash2 class="w-4 h-4 text-muted-foreground" />
                                                        </Button>
                                                    </DialogTrigger>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Delete User</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        <DialogContent>
                                            <form class="space-y-6" @submit="(e) => deleteItem(e, user.id)">
                                                <DialogHeader>
                                                    <DialogTitle>Are you sure you want to delete this user?</DialogTitle>
                                                    <DialogDescription>
                                                        If you delete this user, everything connected to it will also be permanently removed. This action cannot be undone.
                                                    </DialogDescription>
                                                </DialogHeader>

                                                <DialogFooter>
                                                    <DialogClose as-child>
                                                        <Button variant="outline" @click="closeModal">
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>

                                                    <Button variant="destructive" :disabled="form.processing">
                                                        <button type="submit">Delete User</button>
                                                    </Button>
                                                </DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>
                                </TableCell>
                            </TableRow>
                            <TableRow v-else>
                                <TableCell colspan="5" class="text-center text-muted-foreground py-4">No users found.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <div v-if="users.data.length" class="px-2 mt-auto pt-1 flex flex-wrap items-center md:justify-end md:gap-4 lg:gap-10">
                <div class="basis-full text-sm text-muted-foreground shrink-0 mb-4 md:mb-0 md:basis-auto md:me-auto md:order-1">
                    Showing {{ users.from }} to {{ users.to }} of {{ users.total }} records.
                </div>
                <div class="basis-1/3 text-sm font-medium shrink-0 md:basis-auto md:order-3">
                    Page {{ users.current_page }} of {{ users.last_page }}
                </div>
                <div class="basis-2/3 flex items-center justify-end gap-2 md:basis-auto md:order-4">
                    <!-- Active button -->
                    <Link v-if="!isFirstPage" :href="users.first_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronsLeft class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronsLeft class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                     <!-- Active button -->
                    <Link v-if="users.prev_page_url" :href="users.prev_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronLeft class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronLeft class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                    <!-- Active button -->
                    <Link v-if="users.next_page_url" :href="users.next_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronRight class="w-4 h-4 text-muted-foreground/50" />
                    </Button>

                    <!-- Active button -->
                    <Link v-if="!isLastPage" :href="users.last_page_url">
                        <Button variant="outline" size="icon">
                            <ChevronsRight class="w-4 h-4" />
                        </Button>
                    </Link>

                    <!-- Disabled button -->
                    <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                        <ChevronsRight class="w-4 h-4 text-muted-foreground/50" />
                    </Button>
                </div>
                <div class="basis-full text-sm font-medium flex items-center justify-between gap-2 shrink-0 mt-4 md:mt-0 md:basis-auto md:order-2">
                    <span>Rows per page</span>
                    <Select v-model="perPage">
                        <SelectTrigger class="w-[70px]">
                            <SelectValue placeholder="Select..."/>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="20">20</SelectItem>
                            <SelectItem value="30">30</SelectItem>
                            <SelectItem value="40">40</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
