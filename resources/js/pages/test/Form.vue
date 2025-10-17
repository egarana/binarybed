<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import test from '@/routes/test';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';
import { useFilter } from 'reka-ui';

/* Icons */
import {
    PlusCircle,
    X,
    ChevronsUpDown,
    Copy,
    Check,
    Search,
    CalendarIcon,
} from 'lucide-vue-next';

/* UI Components */
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Textarea } from '@/components/ui/textarea';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field';
import { Calendar } from '@/components/ui/calendar';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';

/* Types */
import type { BreadcrumbItem } from '@/types';
import type { DropdownMenuCheckboxItemProps } from 'reka-ui';
import type { DateValue } from '@internationalized/date';

/* Date Utilities */
import { DateFormatter, getLocalTimeZone } from '@internationalized/date';

/* ---------------------------------------------------------
| Breadcrumbs & Page State
--------------------------------------------------------- */
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Test Form', href: test.form() },
];

const page = usePage();
const search = ref(page.props.search || '');
const resetFilters = () => (search.value = '');

/* ---------------------------------------------------------
| Dropdown Menu
--------------------------------------------------------- */
type Checked = DropdownMenuCheckboxItemProps['modelValue'];
const showStatusBar = ref<Checked>(true);
const showActivityBar = ref<Checked>(false);
const showPanel = ref<Checked>(false);

/* ---------------------------------------------------------
| Collapsible
--------------------------------------------------------- */
const isOpen = ref(false);

/* ---------------------------------------------------------
| Combobox
--------------------------------------------------------- */
const frameworks = [
    { value: 'next.js', label: 'Next.js' },
    { value: 'sveltekit', label: 'SvelteKit' },
    { value: 'nuxt', label: 'Nuxt' },
    { value: 'remix', label: 'Remix' },
    { value: 'astro', label: 'Astro' },
];
const value = ref<typeof frameworks[0]>(); // for first combobox

/* ---------------------------------------------------------
| Status Popover
--------------------------------------------------------- */
interface Status {
    value: string;
    label: string;
}
const statuses: Status[] = [
    { value: 'backlog', label: 'Backlog' },
    { value: 'todo', label: 'Todo' },
    { value: 'in progress', label: 'In Progress' },
    { value: 'done', label: 'Done' },
    { value: 'canceled', label: 'Canceled' },
];
const open = ref(false);
const selectedStatus = ref<Status>();

/* ---------------------------------------------------------
| Number Field & Date Picker
--------------------------------------------------------- */
const df = new DateFormatter('en-US', { dateStyle: 'long' });
const datevalue = ref<DateValue>();

/* ---------------------------------------------------------
| Tags Combobox
--------------------------------------------------------- */
const modelValue = ref(['Apple', 'Banana']);
const tagsModelValue = ref<string[]>([]);
const tagsOpen = ref(false);
const searchTerm = ref('');
const { contains } = useFilter({ sensitivity: 'base' });

const filteredFrameworks = computed(() => {
    const options = frameworks.filter(i => !modelValue.value.includes(i.label));
    return searchTerm.value
        ? options.filter(option => contains(option.label, searchTerm.value))
        : options;
});
</script>

<template>
    <Head title="Test Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Input />

            <div class="items-top flex gap-x-2">
                <Checkbox id="terms1" />
                <div class="grid gap-1.5 leading-none">
                    <label
                        for="terms1"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                        Accept terms and conditions
                    </label>
                    <p class="text-sm text-muted-foreground">
                        You agree to our Terms of Service and Privacy Policy.
                    </p>
                </div>
            </div>

            <Collapsible v-model:open="isOpen" class="w-[350px] space-y-2">
                <div class="flex items-center justify-between space-x-4 px-4">
                    <h4 class="text-sm font-semibold">
                        @peduarte starred 3 repositories
                    </h4>
                    <CollapsibleTrigger as-child>
                        <Button variant="ghost" size="sm" class="w-9 p-0">
                            <ChevronsUpDown class="h-4 w-4" />
                            <span class="sr-only">Toggle</span>
                        </Button>
                    </CollapsibleTrigger>
                </div>

                <div class="rounded-md border px-4 py-3 font-mono text-sm">
                    @radix-ui/primitives
                </div>

                <CollapsibleContent class="space-y-2">
                    <div class="rounded-md border px-4 py-3 font-mono text-sm">
                        @radix-ui/colors
                    </div>
                    <div class="rounded-md border px-4 py-3 font-mono text-sm">
                        @stitches/react
                    </div>
                </CollapsibleContent>
            </Collapsible>

            <Button>Button</Button>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="outline">
                        Share
                    </Button>
                </DialogTrigger>

                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Share link</DialogTitle>
                        <DialogDescription>
                            Anyone who has this link will be able to view this.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="flex items-center space-x-2">
                        <div class="grid flex-1 gap-2">
                            <Label for="link" class="sr-only">
                                Link
                            </Label>
                            <Input
                                id="link"
                                default-value="https://shadcn-vue.com/docs/installation"
                                read-only
                            />
                        </div>
                        <Button type="submit" size="sm" class="px-3">
                            <span class="sr-only">Copy</span>
                            <Copy class="w-4 h-4" />
                        </Button>
                    </div>

                    <DialogFooter class="sm:justify-start">
                        <DialogClose as-child>
                            <Button type="button" variant="secondary">
                                Close
                            </Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline">
                        Open
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent class="w-56">
                    <DropdownMenuLabel>Appearance</DropdownMenuLabel>
                    <DropdownMenuSeparator />

                    <DropdownMenuCheckboxItem v-model:model-value="showStatusBar">
                        Status Bar
                    </DropdownMenuCheckboxItem>

                    <DropdownMenuCheckboxItem
                        v-model:model-value="showActivityBar"
                        disabled
                    >
                        Activity Bar
                    </DropdownMenuCheckboxItem>

                    <DropdownMenuCheckboxItem v-model:model-value="showPanel">
                        Panel
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <div class="grid w-full gap-1.5">
                <Label for="message-2">Your message</Label>
                <Textarea id="message-2" class="mt-2" placeholder="Type your message here." />
                <p class="text-sm text-muted-foreground">
                    Your message will be copied to the support team.
                </p>
            </div>

            <Combobox v-model="value" by="label">
                <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button variant="outline" class="justify-between">
                            {{ value?.label ?? 'Select framework' }}
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                </ComboboxAnchor>

                <ComboboxList>
                    <div class="relative w-full max-w-sm items-center">
                        <ComboboxInput
                            class="pl-9 no-focus-ring !ring-0 border-0 border-b shadow-none rounded-none h-10"
                            placeholder="Select framework..."
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>

                    <ComboboxEmpty>
                        No framework found.
                    </ComboboxEmpty>

                    <ComboboxGroup>
                        <ComboboxItem
                            v-for="framework in frameworks"
                            :key="framework.value"
                            :value="framework"
                        >
                            {{ framework.label }}
                            <ComboboxItemIndicator>
                                <Check :class="cn('ml-auto h-4 w-4')" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxList>
            </Combobox>

            <Combobox by="label">
                <ComboboxAnchor>
                    <div class="relative w-full max-w-sm items-center">
                        <ComboboxInput
                            class="pl-9"
                            :display-value="(val) => val?.label ?? ''"
                            placeholder="Select framework..."
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>
                </ComboboxAnchor>

                <ComboboxList>
                    <ComboboxEmpty>
                        No framework found.
                    </ComboboxEmpty>

                    <ComboboxGroup>
                        <ComboboxItem
                            v-for="framework in frameworks"
                            :key="framework.value"
                            :value="framework"
                        >
                            {{ framework.label }}
                            <ComboboxItemIndicator>
                                <Check :class="cn('ml-auto h-4 w-4')" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxList>
            </Combobox>

            <div class="flex items-center space-x-4">
                <p class="text-sm text-muted-foreground">
                    Status
                </p>

                <Popover v-model:open="open">
                    <PopoverTrigger as-child>
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-[150px] justify-start"
                        >
                            <template v-if="selectedStatus">
                                {{ selectedStatus?.label }}
                            </template>
                            <template v-else>
                                + Set status
                            </template>
                        </Button>
                    </PopoverTrigger>

                    <PopoverContent class="p-0" side="right" align="start">
                        <Command>
                            <CommandInput placeholder="Change status..." />

                            <CommandList>
                                <CommandEmpty>
                                    No results found.
                                </CommandEmpty>

                                <CommandGroup>
                                    <CommandItem
                                        v-for="status in statuses"
                                        :key="status.value"
                                        :value="status.value"
                                        @select="() => {
                                            selectedStatus = status;
                                            open = false;
                                        }"
                                    >
                                        {{ status.label }}
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </div>

            <NumberField id="age" :default-value="18" :min="0">
                <Label for="age">Age</Label>
                <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                </NumberFieldContent>
            </NumberField>

            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        variant="outline"
                        :class="cn(
                            'w-[280px] justify-start text-left font-normal',
                            !datevalue && 'text-muted-foreground'
                        )"
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{
                            datevalue
                                ? df.format(datevalue.toDate(getLocalTimeZone()))
                                : "Pick a date"
                        }}
                    </Button>
                </PopoverTrigger>

                <PopoverContent class="w-auto p-0">
                    <Calendar v-model="datevalue" initial-focus />
                </PopoverContent>
            </Popover>

            <Select>
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Select a fruit" />
                </SelectTrigger>

                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Fruits</SelectLabel>

                        <SelectItem value="apple">
                            Apple
                        </SelectItem>
                        <SelectItem value="banana">
                            Banana
                        </SelectItem>
                        <SelectItem value="blueberry">
                            Blueberry
                        </SelectItem>
                        <SelectItem value="grapes">
                            Grapes
                        </SelectItem>
                        <SelectItem value="pineapple">
                            Pineapple
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Tabs default-value="account" class="w-[400px]">
                <TabsList class="grid w-full grid-cols-2">
                    <TabsTrigger value="account">
                        Account
                    </TabsTrigger>
                    <TabsTrigger value="password">
                        Password
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="account">
                    <Card>
                        <CardHeader>
                            <CardTitle>Account</CardTitle>
                            <CardDescription>
                                Make changes to your account here. Click save when you're done.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="space-y-2">
                            <div class="space-y-1">
                                <Label for="name">Name</Label>
                                <Input id="name" default-value="Pedro Duarte" />
                            </div>

                            <div class="space-y-1">
                                <Label for="username">Username</Label>
                                <Input id="username" default-value="@peduarte" />
                            </div>
                        </CardContent>

                        <CardFooter>
                            <Button>Save changes</Button>
                        </CardFooter>
                    </Card>
                </TabsContent>

                <TabsContent value="password">
                    <Card>
                        <CardHeader>
                            <CardTitle>Password</CardTitle>
                            <CardDescription>
                                Change your password here. After saving, you'll be logged out.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="space-y-2">
                            <div class="space-y-1">
                                <Label for="current">Current password</Label>
                                <Input id="current" type="password" />
                            </div>

                            <div class="space-y-1">
                                <Label for="new">New password</Label>
                                <Input id="new" type="password" />
                            </div>
                        </CardContent>

                        <CardFooter>
                            <Button>Save password</Button>
                        </CardFooter>
                    </Card>
                </TabsContent>
            </Tabs>
            
            <Combobox
                v-model="tagsModelValue"
                v-model:open="tagsOpen"
                :ignore-filter="true"
            >
                <ComboboxAnchor as-child>
                    <TagsInput v-model="tagsModelValue" class="px-2 gap-2 w-80">
                        <div class="flex gap-2 flex-wrap items-center">
                            <TagsInputItem
                                v-for="item in tagsModelValue"
                                :key="item"
                                :value="item"
                            >
                                <TagsInputItemText />
                                <TagsInputItemDelete />
                            </TagsInputItem>
                        </div>

                        <ComboboxInput v-model="searchTerm" as-child>
                            <TagsInputInput
                                placeholder="Framework..."
                                @keydown.enter.prevent
                            />
                        </ComboboxInput>
                    </TagsInput>

                    <ComboboxList class="w-[--reka-popper-anchor-width]">
                        <ComboboxEmpty />
                        <ComboboxGroup>
                            <ComboboxItem
                                v-for="framework in filteredFrameworks"
                                :key="framework.value"
                                :value="framework.label"
                                @select.prevent="
                                    (ev) => {
                                        if (typeof ev.detail.value === 'string') {
                                            searchTerm = '';
                                            tagsModelValue.push(ev.detail.value);
                                        }

                                        if (filteredFrameworks.length === 0) {
                                            tagsOpen = false;
                                        }
                                    }
                                "
                            >
                                {{ framework.label }}
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </ComboboxAnchor>
            </Combobox>

        </div>
    </AppLayout>
</template>
