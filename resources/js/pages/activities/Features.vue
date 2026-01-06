<script setup lang="ts">
import activities from '@/routes/activities';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Label } from '@/components/ui/label';

interface Props {
    activity: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        features?: Record<string, ComboboxOption[]>;
    };
    allFeatures?: ComboboxOption[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Activities', href: activities.index.url() },
    { title: props.activity.name, href: activities.edit.url([props.activity.tenant_id, props.activity.slug]) },
    { title: 'Features', href: '#' },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'features',
    action: 'update',
});

// Create refs for each category from existing features
const inclusions = ref<ComboboxOption[]>(props.activity.features?.inclusion || []);
const exclusions = ref<ComboboxOption[]>(props.activity.features?.exclusion || []);
const equipment = ref<ComboboxOption[]>(props.activity.features?.equipment || []);
const requirements = ref<ComboboxOption[]>(props.activity.features?.requirement || []);

// Transform form data to send categorized features
const transformData = (data: Record<string, any>) => ({
    ...data,
    features: {
        inclusion: inclusions.value.map(f => f.value),
        exclusion: exclusions.value.map(f => f.value),
        equipment: equipment.value.map(f => f.value),
        requirement: requirements.value.map(f => f.value),
    },
});
</script>

<template>
    <BaseFormPage
        title="Features"
        :breadcrumbs="breadcrumbs"
        :action="activities.features.sync.url([activity.tenant_id, activity.slug])"
        method="post"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="Product"
                :value="`${activity.name} - ${activity.tenant_name}`"
                help-text="Manage features for this activity"
            />

            <!-- Inclusions -->
            <div class="grid gap-2">
                <Label>Inclusions</Label>
                <p class="text-xs text-muted-foreground -mt-1">What's included (Breakfast, Guide, Insurance)</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="inclusions"
                    :options="allFeatures"
                    :fetch-url="() => activities.features.url([activity.tenant_id, activity.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Inclusions"
                    placeholder="Select inclusions"
                    search-placeholder="Search features..."
                    name="features[inclusion]"
                    :tabindex="1"
                    :error="errors['features.inclusion']"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />
            </div>

            <!-- Exclusions -->
            <div class="grid gap-2">
                <Label>Exclusions</Label>
                <p class="text-xs text-muted-foreground -mt-1">What's not included (Personal expenses, Tips)</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="exclusions"
                    :options="allFeatures"
                    :fetch-url="() => activities.features.url([activity.tenant_id, activity.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Exclusions"
                    placeholder="Select exclusions"
                    search-placeholder="Search features..."
                    name="features[exclusion]"
                    :tabindex="2"
                    :error="errors['features.exclusion']"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />
            </div>

            <!-- Equipment -->
            <div class="grid gap-2">
                <Label>Equipment</Label>
                <p class="text-xs text-muted-foreground -mt-1">Equipment provided (Diving gear, Bicycle)</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="equipment"
                    :options="allFeatures"
                    :fetch-url="() => activities.features.url([activity.tenant_id, activity.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Equipment"
                    placeholder="Select equipment"
                    search-placeholder="Search features..."
                    name="features[equipment]"
                    :tabindex="3"
                    :error="errors['features.equipment']"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />
            </div>

            <!-- Requirements -->
            <div class="grid gap-2">
                <Label>Requirements</Label>
                <p class="text-xs text-muted-foreground -mt-1">Activity requirements (Swimming ability, License)</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="requirements"
                    :options="allFeatures"
                    :fetch-url="() => activities.features.url([activity.tenant_id, activity.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Requirements"
                    placeholder="Select requirements"
                    search-placeholder="Search features..."
                    name="features[requirement]"
                    :tabindex="4"
                    :error="errors['features.requirement']"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="5"
                test-id="update-features-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>
