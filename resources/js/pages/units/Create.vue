<script setup lang="ts">
import units from '@/routes/units';
import { ref, computed, watch } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Plus, Star } from 'lucide-vue-next';

defineProps<{
    tenants?: ComboboxOption[];
    features?: ComboboxOption[];
}>();

const breadcrumbs = [
    { title: 'Units', href: units.index.url() },
    { title: 'Create Unit', href: units.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'unit',
    action: 'create',
});

// Form fields
const selectedTenant = ref<ComboboxOption>();
const selectedFeatures = ref<ComboboxOption[]>([]);

// Control features visibility
const showFeatures = ref(false);
const hasFeatures = computed(() => selectedFeatures.value.length > 0);

// Reset showFeatures if all features are removed
watch(selectedFeatures, (newFeatures) => {
    if (newFeatures.length === 0) {
        showFeatures.value = false;
    }
}, { deep: true });

const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});
</script>

<template>
    <BaseFormPage
        title="Create Unit"
        :breadcrumbs="breadcrumbs"
        :action="units.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <!-- Tenant Selection -->
            <SearchableSelect
                mode="single"
                v-model="selectedTenant"
                :options="tenants"
                :fetch-url="() => units.create.url()"
                response-key="tenants"
                label="Tenant"
                placeholder="Select a tenant"
                search-placeholder="Search tenant..."
                name="tenant_id"
                :tabindex="1"
                :error="errors.tenant_id"
                :required="true"
                :clearable="true"
                :disabled="processing"
            />

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="2"
                autocomplete="organization"
                placeholder="e.g. Unit Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="3"
                autocomplete="off"
                placeholder="e.g. unit-name"
                v-model="slug"
                :error="errors.slug"
            />

            <!-- Features Selection -->
            <div class="grid gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <Label>Features <span class="text-muted-foreground">(Optional)</span></Label>
                        <p class="text-xs text-muted-foreground mt-1">
                            Add features to highlight your unit's amenities and characteristics.
                        </p>
                    </div>
                </div>

                <Empty v-if="!showFeatures && !hasFeatures" class="border border-dashed">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Star />
                        </EmptyMedia>
                        <EmptyTitle>No features selected</EmptyTitle>
                        <EmptyDescription>
                            Add features to enhance your unit listing.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button type="button" variant="outline" @click="showFeatures = true">
                            <Plus /> Add Features
                        </Button>
                    </EmptyContent>
                </Empty>

                <SearchableSelect
                    v-if="showFeatures || hasFeatures"
                    mode="multiple"
                    v-model="selectedFeatures"
                    :options="features"
                    :fetch-url="() => units.create.url()"
                    response-key="features"
                    search-param="search"
                    label="Features"
                    placeholder="Select features"
                    search-placeholder="Search features..."
                    name="features"
                    :tabindex="4"
                    :error="errors.features"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />

                <!-- Flag to indicate features have been cleared (for empty array detection) -->
                <input
                    v-if="selectedFeatures.length === 0"
                    type="hidden"
                    name="_features_cleared"
                    value="1"
                />
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="5"
                test-id="create-unit-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>