<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { onMounted, onUnmounted } from 'vue';

const page = usePage()
const permissions = page.props.permissions as Array<{ id: number; name: string }>

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles & Permissions',
        href: '/roles',
    },
    {
        title: 'Add Role',
        href: '/roles/create',
    },
];

const form = useForm({
    name: '',
    permissions: [],
});

const submit = () => {
    form.post(route('roles.store'), {
        preserveScroll: false,
        onSuccess: () => {
            toast('Role created', {
                description: 'The role has been created successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: () => {
            toast('Error creating role', {
                description: 'Something went wrong, please try again',
                action: {
                    label: 'Close',
                },
            })
        },
    });
};

const togglePermission = (value) => {
    if (form.permissions.includes(value)) {
        form.permissions = form.permissions.filter((permission) => permission !== value);
    } else {
        form.permissions.push(value);
    }
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
    <Head title="Add Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex flex-col space-y-6 min-h-full">
                <HeadingSmall title="Add role" description="Define a role name and assign permissions to control access" />

                <Separator />
    
                <form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Role name (e.g. super-admin, editor)" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Permissions</Label>
                        <div v-if="permissions.length" class="mt-1 grid grid-cols-1 gap-2.5 md:grid-cols-2 md:gap-3 lg:grid-cols-4 lg:col-span-2">
                            <Label 
                                :id="permission.value"
                                v-for="(permission, index) in permissions" 
                                :key="index" :for="permission.value" 
                                class="flex flex-row items-start gap-x-3 space-y-0 rounded-md border p-4 hover:cursor-pointer hover:bg-muted/50 shadow-xs"
                            >
                                <Checkbox
                                    :id="permission.name"
                                    :value="permission.id"
                                    :model-value="form.permissions.includes(permission.id)"
                                    @update:model-value="togglePermission(permission.id)"
                                    class="border-foreground"
                                />
                                <div class="leading-none">
                                    <p class="text-sm font-normal leading-none block peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ permission.name }}</p>
                                </div>
                            </Label>
                        </div>
                        <p v-else class="text-muted-foreground text-sm mt-1">No permissions available.</p>
                    </div>

                    <div class="mt-auto text-right">
                        <Button :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
