<script setup lang="ts">
import activities from '@/routes/activities';
import { ref, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

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
const suggestions = ref<ComboboxOption[]>(props.activity.features?.suggestion || []);

// Computed excluded values for each category - includes values from OTHER categories
const inclusionsExcluded = computed(() => 
    [...exclusions.value, ...equipment.value, ...requirements.value, ...suggestions.value].map(f => f.value)
);
const exclusionsExcluded = computed(() => 
    [...inclusions.value, ...equipment.value, ...requirements.value, ...suggestions.value].map(f => f.value)
);
const equipmentExcluded = computed(() => 
    [...inclusions.value, ...exclusions.value, ...requirements.value, ...suggestions.value].map(f => f.value)
);
const requirementsExcluded = computed(() => 
    [...inclusions.value, ...exclusions.value, ...equipment.value, ...suggestions.value].map(f => f.value)
);
const suggestionsExcluded = computed(() => 
    [...inclusions.value, ...exclusions.value, ...equipment.value, ...requirements.value].map(f => f.value)
);

// Transform form data to send categorized features
const transformData = (data: Record<string, any>) => ({
    ...data,
    features: {
        inclusion: inclusions.value.map(f => f.value),
        exclusion: exclusions.value.map(f => f.value),
        equipment: equipment.value.map(f => f.value),
        requirement: requirements.value.map(f => f.value),
        suggestion: suggestions.value.map(f => f.value),
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

            <Tabs default-value="inclusions">
                <TabsList>
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
                    <TabsTrigger value="equipment">
                        Equipment
                        <Badge variant="secondary" class="ml-1.5">
                            {{ equipment.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="requirements">
                        Requirements
                        <Badge variant="secondary" class="ml-1.5">
                            {{ requirements.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="suggestions">
                        Suggestions
                        <Badge variant="secondary" class="ml-1.5">
                            {{ suggestions.length }}
                        </Badge>
                    </TabsTrigger>
                </TabsList>

                <!-- Inclusions Tab -->
                <TabsContent value="inclusions" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">What's included (Breakfast, Guide, Insurance)</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="inclusions"
                        :options="[]"
                        :exclude-values="inclusionsExcluded"
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
                </TabsContent>

                <!-- Exclusions Tab -->
                <TabsContent value="exclusions" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">What's not included (Personal expenses, Tips)</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="exclusions"
                        :options="[]"
                        :exclude-values="exclusionsExcluded"
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
                </TabsContent>

                <!-- Equipment Tab -->
                <TabsContent value="equipment" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">Equipment provided (Diving gear, Bicycle)</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="equipment"
                        :options="[]"
                        :exclude-values="equipmentExcluded"
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
                </TabsContent>

                <!-- Requirements Tab -->
                <TabsContent value="requirements" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">Activity requirements (Swimming ability, License)</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="requirements"
                        :options="[]"
                        :exclude-values="requirementsExcluded"
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
                </TabsContent>

                <!-- Suggestions Tab -->
                <TabsContent value="suggestions" class="space-y-4 mt-4">
                    <p class="text-sm text-muted-foreground">Suggested items to bring (Camera, Sunscreen, Cash)</p>
                    <SearchableSelect
                        mode="multiple"
                        v-model="suggestions"
                        :options="[]"
                        :exclude-values="suggestionsExcluded"
                        :fetch-url="() => activities.features.url([activity.tenant_id, activity.slug])"
                        response-key="allFeatures"
                        search-param="search"
                        label="Suggestions"
                        placeholder="Select suggested items"
                        search-placeholder="Search features..."
                        name="features[suggestion]"
                        :tabindex="5"
                        :error="errors['features.suggestion']"
                        :required="false"
                        :draggable="true"
                        :disabled="processing"
                        :show-label="false"
                    />
                </TabsContent>
            </Tabs>

            <SubmitButton
                :processing="processing"
                :tabindex="6"
                test-id="update-features-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>

