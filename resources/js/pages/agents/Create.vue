<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import agents from '@/routes/agents';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';
import { useShortcut } from '@/composables/useShortcut';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agents',
        href: agents.index.url(),
    },
    {
        title: 'Create Agent',
        href: agents.create.url(),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const name = ref('');
const email = ref('');
const password = ref('');

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'create', capitalizeFirst('agent'), payload, {
        successDescription: 'The agent has been created successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'create', 'agent', payload, {
        errorDescription: 'An unexpected error occurred while creating the agent. Please check your input and try again.',
    });
};
</script>

<template>
    <Head title="Create Agent" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <Form
                ref="formRef"
                :action="agents.store()"
                method="post"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        :tabindex="1"
                        autocomplete="organization"
                        placeholder="e.g. Agent Name"
                        v-model="name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="e.g. john@example.com"
                        v-model="email"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        :tabindex="3"
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                        v-model="password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="mt-auto text-right pt-6">
                    <Button
                        type="submit"
                        tabindex="4"
                        :disabled="processing"
                        data-test="create-agent-button"
                    >
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