<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import resources from '@/routes/resources'; // 🔧 Ganti dengan route modul (misal: users, properties, reservations)
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { formFor } from '@/helpers/formFor';
import ResourceController from '@/actions/App/Http/Controllers/ResourceController'; // 🔧 Ganti dengan controller modul (misal: UserController, PropertyController)
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';
import { toast } from 'vue-sonner';
import { notifyActionResult } from '@/helpers/notifyActionResult';

// 🔧 Ubah judul breadcrumb & URL sesuai halaman
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Resources', // 🔧 Ubah: nama parent page
        href: resources.index(), // 🔧 Ubah: route index modul
    },
    {
        title: 'Create Resource', // 🔧 Ubah: judul halaman saat ini
        href: resources.create(), // 🔧 Ubah: route create modul
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'create', 'Resource', payload, { // 🔧 Ubah: 'Resource' -> nama entity modul (misal: 'User', 'Property', 'Reservation')
        successDescription: 'The resource has been created successfully.', // 🔧 Ubah: deskripsi sukses sesuai kebutuhan
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'create', 'resource', payload, { // 🔧 Ubah: 'resource' -> nama entity modul (misal: 'user', 'property', 'reservation')
        errorDescription: 'An unexpected error occurred while creating the resource. Please check your input and try again.', // 🔧 Ubah: deskripsi error sesuai kebutuhan
    });
};
</script>

<template>
    <!-- 🔧 Ubah title halaman di tab browser -->
    <Head title="Create Resource" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <!-- 🔧 Ganti controller sesuai modul -->
            <Form
                ref="formRef"
                v-bind="formFor(ResourceController.store.post())"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <!-- 🔧 Field input -->
                <div class="grid gap-2">
                    <Label for="resource_name">Resource name</Label>
                    <Input
                        id="resource_name"
                        name="resource_name"
                        type="text"
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="e.g. Resource Name"
                    />
                    <InputError :message="errors.resource_name" />
                </div>

                <!-- 🔧 Tombol submit -->
                <div class="mt-auto text-right pt-6">
                    <Button
                        type="submit"
                        tabindex="5"
                        :disabled="processing"
                        data-test="create-resource-button" <!-- 🔧 Ubah ID sesuai modul -->
                    />
                        <LoaderCircle
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Create
                    </Button>
                </div>
            </Form>

        </div>
    </AppLayout>
</template>
