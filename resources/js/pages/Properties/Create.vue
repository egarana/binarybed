<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { computed, onMounted, onUnmounted } from 'vue';
import { ref, watch } from "vue"
import {
    Combobox,
    ComboboxAnchor,
    ComboboxInput,
    ComboboxItem,
    ComboboxList
} from "@/components/ui/combobox"
import {
    TagsInput,
    TagsInputItem,
    TagsInputItemDelete,
} from "@/components/ui/tags-input"
import { debounce } from 'lodash-es'
import { X } from "lucide-vue-next"
import { useInitials } from '@/composables/useInitials';
import Avatar from "@/components/ui/avatar/Avatar.vue"
import AvatarImage from "@/components/ui/avatar/AvatarImage.vue"
import AvatarFallback from "@/components/ui/avatar/AvatarFallback.vue"
import ComboboxGroup from '@/components/ui/combobox/ComboboxGroup.vue';

const page = usePage()
const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Properties',
        href: '/properties',
    },
    {
        title: 'Add Property',
        href: '/properties/create',
    },
];

interface UserOption {
    value: string;
    label: {
        name: string;
        email: string;
    };
}

const users = ref<UserOption[]>([])
const comboboxSearchTerm = ref('')
const open = ref(false)
const isLoading = ref(false)

const form = useForm({
    name: '',
    domain: '',
    slug: '',
    users: [] as UserOption[],
});

const { getInitials } = useInitials();

const fetchComboboxData = debounce(() => {
    isLoading.value = true

    router.get(route('properties.create'), {
        search: comboboxSearchTerm.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['users'],
        onSuccess: () => {
            const newProps = usePage().props.users as UserOption[] ?? []
            users.value = newProps
        },
        onFinish: () => {
            isLoading.value = false
        }
    })
}, 300)

watch(comboboxSearchTerm, (term) => {
    if (!term) {
        users.value = []
        return
    }
    fetchComboboxData()
})

const submit = () => {
    form.post(route('properties.store'), {
        preserveScroll: false,
        onSuccess: () => {
            toast('Property created', {
                description: 'The property has been created successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: () => {
            toast('Error creating property', {
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
</script>

<template>
    <Head title="Add Property" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex flex-col space-y-6 min-h-full">
                <HeadingSmall title="Add property" description="Enter the property information and select users to grant access" />

                <Separator />
    
                <form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="e.g., The Grand Hotel, Lux Villa" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="domain">Domain</Label>
                        <Input id="domain" class="mt-1 block w-full" v-model="form.domain" required autocomplete="domain" placeholder="e.g., grandhotel.com (no https:// or www)" />
                        <InputError :message="form.errors.domain" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">Slug</Label>
                        <Input id="slug" class="mt-1 block w-full" v-model="form.slug" required autocomplete="slug" placeholder="e.g., grandhotel" />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="grid">
                        <Label>Users</Label>
                        <Combobox v-model="form.users" v-model:open="open" class="mt-3 combobox-tags-input-wrapper">
                            <ComboboxAnchor as-child class="rounded-none h-auto !p-0 !block !w-full">
                                <ComboboxInput 
                                    v-model="comboboxSearchTerm"
                                    placeholder="Type to search and add users"
                                />
                            </ComboboxAnchor>

                            <ComboboxList align="start" :class="users.length < 1 ? 'border-none' : ''" class="w-full min-w-[200px]">
                                <ComboboxGroup :class="users.length < 1 ? 'p-0' : ''">
                                    <ComboboxItem
                                        v-for="(user, index) in users"
                                        :key="user.value"
                                        :value="user"
                                        @select.prevent="() => {
                                            const exists = form.users.some(u => u.value === user.value)
                                            if (!exists) {
                                                form.users.push(user)
                                            }
                                            comboboxSearchTerm = ''
                                            open = false
                                        }"
                                    >
                                        {{ user.label.name }}
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>

                        <div v-if="form.users.length > 0" class="mt-4 pt-4 border-t text-sm">
                            <h4 class="font-medium">People with access</h4>
                            <div class="mt-4 grid gap-6">
                                <TagsInput v-model="form.users" class="grid border-none p-0 gap-6">
                                    <TagsInputItem v-for="item in form.users" :key="item.value" :value="item" class="bg-transparent h-auto">
                                        <div class="flex items-center justify-start gap-4 w-full">
                                            <Avatar class="overflow-hidden rounded-lg h-10 w-10 text-sm">
                                                <AvatarImage
                                                    v-if="item.label?.avatar"
                                                    :src="item.label.avatar"
                                                    :alt="item.label.name"
                                                />
                                                <AvatarFallback class="rounded-lg text-black dark:text-white">
                                                    {{ getInitials(item.label?.name ?? 'System') }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div  class="max-w-[120px] md:max-w-none">
                                                <p class="font-medium leading-none line-clamp-1">{{ item.label.name }}</p>
                                                <p class="text-muted-foreground line-clamp-1 truncate">{{ item.label.email }}</p>
                                            </div>
                                            <TagsInputItemDelete class="ms-auto">
                                                <Button variant="outline">
                                                    Remove
                                                    <X/>
                                                </Button>
                                            </TagsInputItemDelete>
                                        </div>
                                    </TagsInputItem>
                                </TagsInput>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto text-right pt-6">
                        <Button :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
