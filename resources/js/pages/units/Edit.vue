<script setup lang="ts">
import units from '@/routes/units';
import { ref, computed, watch } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Plus, Star } from 'lucide-vue-next';

interface Props {
    unit: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        features?: ComboboxOption[];
    };
    features?: ComboboxOption[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Units', href: units.index.url() },
    { title: 'Edit Unit', href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'unit',
    action: 'update',
});

// Form fields
const selectedFeatures = ref<ComboboxOption[]>(props.unit.features || []);

// Control features visibility
const showFeatures = ref(selectedFeatures.value.length > 0);
const hasFeatures = computed(() => selectedFeatures.value.length > 0);

// Reset showFeatures if all features are removed
watch(selectedFeatures, (newFeatures) => {
    if (newFeatures.length === 0) {
        showFeatures.value = false;
    }
}, { deep: true });

const name = ref(props.unit.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.unit.slug || ''
});
</script>

<template>
    <BaseFormPage
        :title="`Edit Unit: ${unit.name}`"
        :breadcrumbs="breadcrumbs"
        :action="units.update.url([unit.tenant_id, unit.slug])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <input type="hidden" name="tenant_id" :value="unit.tenant_id" />

            <DisabledFormField
                label="Tenant"
                :value="unit.tenant_name"
                help-text="Unit tenant cannot be changed after creation"
            />

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="1"
                autocomplete="organization"
                placeholder="e.g. Unit Name"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="2"
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
                    :fetch-url="() => units.edit.url([unit.tenant_id, unit.slug])"
                    response-key="features"
                    search-param="search"
                    label="Features"
                    placeholder="Select features"
                    search-placeholder="Search features..."
                    name="features"
                    :tabindex="3"
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
                :tabindex="4"
                test-id="update-unit-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>