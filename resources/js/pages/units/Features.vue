<script setup lang="ts">
import units from '@/routes/units';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Label } from '@/components/ui/label';

interface Props {
    unit: {
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
    { title: 'Units', href: units.index.url() },
    { title: props.unit.name, href: units.edit.url([props.unit.tenant_id, props.unit.slug]) },
    { title: 'Features', href: '#' },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'features',
    action: 'update',
});

// Create refs for each category from existing features
const amenities = ref<ComboboxOption[]>(props.unit.features?.amenity || []);
const facilities = ref<ComboboxOption[]>(props.unit.features?.facility || []);
const inclusions = ref<ComboboxOption[]>(props.unit.features?.inclusion || []);
const exclusions = ref<ComboboxOption[]>(props.unit.features?.exclusion || []);

// Transform form data to send categorized features
const transformData = (data: Record<string, any>) => ({
    ...data,
    features: {
        amenity: amenities.value.map(f => f.value),
        facility: facilities.value.map(f => f.value),
        inclusion: inclusions.value.map(f => f.value),
        exclusion: exclusions.value.map(f => f.value),
    },
});
</script>

<template>
    <BaseFormPage
        title="Features"
        :breadcrumbs="breadcrumbs"
        :action="units.features.sync.url([unit.tenant_id, unit.slug])"
        method="post"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <DisabledFormField
                label="Product"
                :value="`${unit.name} - ${unit.tenant_name}`"
                help-text="Manage features for this unit"
            />

            <!-- Amenities -->
            <div class="grid gap-2">
                <Label>Amenities</Label>
                <p class="text-xs text-muted-foreground -mt-1">Unit amenities like WiFi, AC, Kitchen</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="amenities"
                    :options="allFeatures"
                    :fetch-url="() => units.features.url([unit.tenant_id, unit.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Amenities"
                    placeholder="Select amenities"
                    search-placeholder="Search features..."
                    name="features[amenity]"
                    :tabindex="1"
                    :error="errors['features.amenity']"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />
            </div>

            <!-- Facilities -->
            <div class="grid gap-2">
                <Label>Facilities</Label>
                <p class="text-xs text-muted-foreground -mt-1">Property facilities like Pool, Parking, Gym</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="facilities"
                    :options="allFeatures"
                    :fetch-url="() => units.features.url([unit.tenant_id, unit.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Facilities"
                    placeholder="Select facilities"
                    search-placeholder="Search features..."
                    name="features[facility]"
                    :tabindex="2"
                    :error="errors['features.facility']"
                    :required="false"
                    :draggable="true"
                    :disabled="processing"
                    :show-label="false"
                />
            </div>

            <!-- Inclusions -->
            <div class="grid gap-2">
                <Label>Inclusions</Label>
                <p class="text-xs text-muted-foreground -mt-1">What's included in the stay</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="inclusions"
                    :options="allFeatures"
                    :fetch-url="() => units.features.url([unit.tenant_id, unit.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Inclusions"
                    placeholder="Select inclusions"
                    search-placeholder="Search features..."
                    name="features[inclusion]"
                    :tabindex="3"
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
                <p class="text-xs text-muted-foreground -mt-1">What's not included</p>
                <SearchableSelect
                    mode="multiple"
                    v-model="exclusions"
                    :options="allFeatures"
                    :fetch-url="() => units.features.url([unit.tenant_id, unit.slug])"
                    response-key="allFeatures"
                    search-param="search"
                    label="Exclusions"
                    placeholder="Select exclusions"
                    search-placeholder="Search features..."
                    name="features[exclusion]"
                    :tabindex="4"
                    :error="errors['features.exclusion']"
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
