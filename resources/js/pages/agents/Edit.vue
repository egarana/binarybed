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

interface Props {
    agent: {
        id: number;
        code: string;
        name: string;
        email: string;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agents',
        href: agents.index.url(),
    },
    {
        title: 'Edit Agent',
        href: agents.edit.url(props.agent.id),
    },
];

const formRef = ref<InstanceType<typeof Form> | null>(null);

// Form fields
const name = ref(props.agent.name || '');
const email = ref(props.agent.email || '');
const password = ref('');

useShortcut({
    keys: ['ctrl+s', 'meta+s'],
    callback: () => {
        formRef.value?.$el?.requestSubmit?.();
    },
});

const onSuccess = (payload: any) => {
    notifyActionResult('success', 'update', capitalizeFirst('agent'), payload, {
        successDescription: 'The agent has been updated successfully.',
    });
};

const onError = (payload: any) => {
    notifyActionResult('error', 'update', 'agent', payload, {
        errorDescription: 'An unexpected error occurred while updating the agent. Please check your input and try again.',
    });
};
</script>

<template>
    <Head :title="`Edit Agent: ${agent.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            
            <Form
                ref="formRef"
                :action="agents.update(agent.id)"
                method="put"
                @success="onSuccess"
                @error="onError"
                class="space-y-6 h-full flex flex-col"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <h1 class="disabled-label">Agent Code</h1>
                    <div class="disabled-input">
                        {{ agent.code }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Agent code cannot be changed after creation
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="e.g. John Doe"
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
                        placeholder="Leave blank to keep current password"
                        v-model="password"
                    />
                    <InputError :message="errors.password" />
                    <p class="text-xs text-muted-foreground">
                        Only fill this field if you want to change the password (minimum 8 characters)
                    </p>
                </div>

                <div class="mt-auto text-right pt-6">
                    <Button
                        type="submit"
                        tabindex="4"
                        :disabled="processing"
                        data-test="update-agent-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Save
                    </Button>
                </div>
            </Form>

        </div>
    </AppLayout>
</template>
