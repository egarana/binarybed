<script setup lang="ts">
import units from '@/routes/units';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

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

// Computed excluded values for each category - includes values from OTHER categories
const amenitiesExcluded = computed(() => 
    [...facilities.value, ...inclusions.value, ...exclusions.value].map(f => f.value)
);
const facilitiesExcluded = computed(() => 
    [...amenities.value, ...inclusions.value, ...exclusions.value].map(f => f.value)
);
const inclusionsExcluded = computed(() => 
    [...amenities.value, ...facilities.value, ...exclusions.value].map(f => f.value)
);
const exclusionsExcluded = computed(() => 
    [...amenities.value, ...facilities.value, ...inclusions.value].map(f => f.value)
);

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

            <Tabs default-value="amenities">
                <TabsList>
                    <TabsTrigger value="amenities">
                        Amenities
                        <Badge variant="secondary" class="ml-1.5">
                            {{ amenities.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="facilities">
                        Facilities
                        <Badge variant="secondary" class="ml-1.5">
                            {{ facilities.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="inclusions">
                        Inclusions
                        <Badge variant="secondary" class="ml-1.5">
                            {{ inclusions.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="exclusions">
                        Exclusions
                        <Badge variant="secondary" class="ml-1.5">
                            {{ exclusions.length }}
                        </Badge>
                    </TabsTrigger>
                </TabsList>

                <!-- Amenities Tab -->
                <TabsContent value="amenities" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">Unit amenities like WiFi, AC, Kitchen</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="amenities"
                        :options="allFeatures"
                        :exclude-values="amenitiesExcluded"
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
                </TabsContent>

                <!-- Facilities Tab -->
                <TabsContent value="facilities" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">Property facilities like Pool, Parking, Gym</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="facilities"
                        :options="allFeatures"
                        :exclude-values="facilitiesExcluded"
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
                </TabsContent>

                <!-- Inclusions Tab -->
                <TabsContent value="inclusions" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">What's included in the stay</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="inclusions"
                        :options="allFeatures"
                        :exclude-values="inclusionsExcluded"
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
                </TabsContent>

                <!-- Exclusions Tab -->
                <TabsContent value="exclusions" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">What's not included</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="exclusions"
                        :options="allFeatures"
                        :exclude-values="exclusionsExcluded"
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
                </TabsContent>
            </Tabs>

            <SubmitButton
                :processing="processing"
                :tabindex="5"
                test-id="update-features-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>

