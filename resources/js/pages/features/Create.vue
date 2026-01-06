<script setup lang="ts">
import features from '@/routes/features';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import FormField from '@/components/FormField.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import SubmitButton from '@/components/SubmitButton.vue';

const breadcrumbs = [
    { title: 'Features', href: features.index.url() },
    { title: 'Create Feature', href: features.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'feature',
    action: 'create',
});

// Form fields
const name = ref('');
const { slug: value } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});
const description = ref('');
const icon = ref('');
</script>

<template>
    <BaseFormPage
        title="Create Feature"
        :breadcrumbs="breadcrumbs"
        :action="features.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="off"
                placeholder="e.g. Free WiFi"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="value"
                label="Value (Unique Identifier)"
                type="text"
                :tabindex="2"
                autocomplete="off"
                placeholder="e.g. wifi"
                v-model="value"
                :error="errors.value"
            />

            <div class="grid gap-2">
                <Label for="description">Description <span class="text-muted-foreground">(Optional)</span></Label>
                <Textarea
                    id="description"
                    name="description"
                    :tabindex="3"
                    placeholder="Optional description"
                    v-model="description"
                    rows="6"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2">
                <Label for="icon">Icon (SVG or class) <span class="text-muted-foreground">(Optional)</span></Label>
                <Textarea
                    id="icon"
                    name="icon"
                    :tabindex="4"
                    placeholder="e.g. <svg>...</svg> or lucide-wifi"
                    v-model="icon"
                    rows="6"
                />
                <InputError :message="errors.icon" />
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="5"
                test-id="create-feature-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>
