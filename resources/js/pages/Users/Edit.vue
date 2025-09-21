<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage()
const user = page.props.user as { name: string; roles: Array<{ id: number; name: string }> };
const roles = page.props.roles as Array<{ id: number; name: string }>

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Edit User',
        href: route('users.edit', user.id),
    },
];

const form = useForm({
    name: user.name,
    email: user.email,
    password: user.password,
    roles: user.roles.map((p) => p.id),
});

const submit = () => {
    form.put(route('users.update', user.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast('User edited', {
                description: 'The user has been edited successfully',
                action: {
                    label: 'Close',
                },
            })
        },
        onError: () => {
            toast('Error editing user', {
                description: 'Something went wrong, please try again',
                action: {
                    label: 'Close',
                },
            })
        },
    });
};

const toggleRole = (value) => {
    if (form.roles.includes(value)) {
        form.roles = form.roles.filter((role) => role !== value);
    } else {
        form.roles.push(value);
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
    <Head title="Add User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex flex-col space-y-6 min-h-full">
                <HeadingSmall title="Add user" description="Create a new user and define access control through roles and permissions" />

                <Separator />

                <form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
                    <div class="grid gap-x-4 gap-y-6 grid-cols-1 lg:gap-y-4 lg:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" class="mt-1 block w-full" v-model="form.name" autocomplete="name" placeholder="e.g. Sarah Lee" />
                            <InputError :message="form.errors.name" />
                        </div>
    
                        <div class="flex flex-col gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" autocomplete="email" placeholder="e.g. sarahlee@example.com" />
                            <InputError :message="form.errors.email" />
                        </div>
    
                        <div class="flex flex-col gap-2 lg:col-span-2">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" class="mt-1 block w-full" v-model="form.password" placeholder="Enter a strong password (min. 8 characters)" />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2 lg:col-span-2">
                            <Label>Roles</Label>
                            <div class="mt-1 grid grid-cols-1 gap-2.5 md:grid-cols-2 md:gap-3 lg:grid-cols-4 lg:col-span-2">
                                <Label 
                                    v-for="(role, index) in roles" 
                                    :key="index" :for="role.value" 
                                    class="flex flex-row items-start gap-x-3 space-y-0 rounded-md border p-4 hover:cursor-pointer hover:bg-muted/50 shadow-xs"
                                >
                                    <Checkbox
                                        :id="role.name"
                                        :value="role.id"
                                        :model-value="form.roles.includes(role.id)"
                                        @update:model-value="toggleRole(role.id)"
                                        class="border-foreground"
                                    />
                                    <div class="leading-none">
                                        <p class="text-sm font-normal leading-none block peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ role.name }}</p>
                                    </div>
                                </Label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto text-right lg:col-span-2">
                        <Button :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
