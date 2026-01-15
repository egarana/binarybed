<script setup lang="ts">
import units from '@/routes/units';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import CurrencySelect from '@/components/CurrencySelect.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import SellingPointsEditor from '@/components/SellingPointsEditor.vue';
import LocationHighlightsEditor from '@/components/LocationHighlightsEditor.vue';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

defineProps<{
    tenants?: ComboboxOption[];
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

const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});

// Track uploaded media IDs from ImageUploader
const uploadedMediaIds = ref<number[]>([]);

// Standard Rate fields
// Standard Rate fields
const standardRatePrice = ref(0);
const standardRateCurrency = ref('IDR');
const standardRatePriceType = ref('flat');
const description = ref('');

interface SellingPoint {
    icon: string;
    title: string;
    description: string;
    _id?: string;
}

const sellingPoints = ref<SellingPoint[]>([]);

// Unit Capacity & Details
const subtitle = ref('');
const maxGuests = ref(2);
const bedroomCount = ref(1);
const bathroomCount = ref(1);
const view = ref('');

// Location
const locationAddress = ref('');
const locationSubtitle = ref('');
const locationMapUrl = ref('');
const locationHighlights = ref<string[]>([]);

// Error tracking for badge indicators
const hasGeneralErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.tenant_id) count++;
    if (errors?.name) count++;
    if (errors?.slug) count++;
    return count;
};

const hasDescriptionErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.subtitle) count++;
    if (errors?.description) count++;
    return count;
};

const hasPropertyDetailsErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.max_guests) count++;
    if (errors?.bedroom_count) count++;
    if (errors?.bathroom_count) count++;
    if (errors?.view) count++;
    if (errors?.capacity) count++;
    return count;
};

const hasPricingErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.standard_rate_price) count++;
    if (errors?.standard_rate_currency) count++;
    if (errors?.standard_rate_price_type) count++;
    return count;
};

const hasMediaErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.images) count++;
    if (errors?.uploaded_media_ids) count++;
    return count;
};

const hasSellingPointsErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.selling_points) count++;
    return count;
};

const hasLocationErrors = (errors: Record<string, any>) => {
    let count = 0;
    // Check for location-related errors (location.address, location.subtitle, etc.)
    Object.keys(errors).forEach(key => {
        if (key.startsWith('location.') || key === 'location') {
            count++;
        }
    });
    return count;
};

// Transform function to prepare ALL data for submission
// This ensures data is sent regardless of which tab is active (TabsContent unmounts inactive tabs)
function transformFormData(data: Record<string, any>) {
    const filteredHighlights = locationHighlights.value.filter(h => h?.trim());
    const hasLocationData = locationAddress.value || locationSubtitle.value || locationMapUrl.value || filteredHighlights.length > 0;

    const formData: Record<string, any> = {
        ...data,
        // General tab
        tenant_id: selectedTenant.value?.value ?? '',
        name: name.value,
        slug: slug.value,
        // Description tab
        subtitle: subtitle.value,
        description: description.value,
        // Property Details tab
        max_guests: maxGuests.value,
        bedroom_count: bedroomCount.value,
        bathroom_count: bathroomCount.value,
        view: view.value,
        // Pricing tab
        standard_rate_price: standardRatePrice.value,
        standard_rate_currency: standardRateCurrency.value,
        standard_rate_price_type: standardRatePriceType.value,
        // Media tab
        uploaded_media_ids: uploadedMediaIds.value,
        // Selling Points tab
        selling_points: sellingPoints.value
            .filter(sp => sp.title?.trim())
            .map(({ icon, title, description }) => ({ icon, title, description })),
    };

    // Only include location if at least one field has value
    if (hasLocationData) {
        formData.location = {
            address: locationAddress.value,
            subtitle: locationSubtitle.value,
            map_url: locationMapUrl.value,
            highlights: filteredHighlights
        };
    }

    return formData;
}

</script>

<template>
    <BaseFormPage
        title="Create Unit"
        :breadcrumbs="breadcrumbs"
        :action="units.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="transformFormData"
    >
        <template #default="{ errors, processing }">
            <Tabs default-value="general">
                <TabsList>
                    <TabsTrigger value="general">
                        General
                        <Badge v-if="hasGeneralErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasGeneralErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="description">
                        Description
                        <Badge v-if="hasDescriptionErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasDescriptionErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="property-details">
                        Property Details
                        <Badge v-if="hasPropertyDetailsErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasPropertyDetailsErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="pricing">
                        Pricing
                        <Badge v-if="hasPricingErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasPricingErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="selling-points">
                        Selling Points
                        <Badge v-if="hasSellingPointsErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasSellingPointsErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="location">
                        Location
                        <Badge v-if="hasLocationErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasLocationErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="media">
                        Media
                        <Badge v-if="hasMediaErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasMediaErrors(errors) }}</Badge>
                    </TabsTrigger>
                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="space-y-4 mt-4">
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
                </TabsContent>

                <!-- Description Tab -->
                <TabsContent value="description" class="space-y-4 mt-4">
                    <FormField
                        id="subtitle"
                        label="Subtitle"
                        type="text"
                        :tabindex="1"
                        autocomplete="off"
                        placeholder="e.g. Entire cabin"
                        v-model="subtitle"
                        :error="errors.subtitle"
                        :optional="true"
                    />

                    <div class="grid gap-2">
                        <Label for="description" class="flex items-center gap-1">
                            Description
                            <span class="text-muted-foreground">(Optional)</span>
                        </Label>
                        <Textarea
                            id="description"
                            name="description"
                            :tabindex="2"
                            placeholder="Describe this unit..."
                            v-model="description"
                            rows="12"
                        />
                        <InputError :message="errors.description" />
                    </div>
                </TabsContent>

                <!-- Property Details Tab -->
                <TabsContent value="property-details" class="space-y-4 mt-4">
                    <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                        <NumberFormField
                            id="max_guests"
                            label="Max Guests"
                            :tabindex="1"
                            v-model="maxGuests"
                            :min="1"
                            :max="50"
                            :required="true"
                        />

                        <NumberFormField
                            id="bedroom_count"
                            label="Total Bedrooms"
                            :tabindex="2"
                            v-model="bedroomCount"
                            :min="0"
                            :max="20"
                            :required="true"
                        />

                        <NumberFormField
                            id="bathroom_count"
                            label="Total Bathrooms"
                            :tabindex="3"
                            v-model="bathroomCount"
                            :min="0"
                            :max="20"
                            :required="true"
                        />

                        <div class="col-span-3" v-if="errors.capacity">
                            <InputError :message="errors.capacity" />
                        </div>
                    </div>

                    <FormField
                        id="view"
                        label="View"
                        type="text"
                        :tabindex="4"
                        placeholder="e.g. Lake View"
                        v-model="view"
                        :error="errors.view"
                        :required="true"
                    />
                </TabsContent>

                <!-- Pricing Tab -->
                <TabsContent value="pricing" class="space-y-4 mt-4">
                    <div>
                        <Label>Standard Rate</Label>
                        <p class="text-xs text-muted-foreground mt-1">
                            Set the default rate for this unit. This rate cannot be deleted but can be edited later.
                        </p>
                    </div>
                    
                    <NumberFormField
                        id="standard_rate_price"
                        label="Price"
                        :tabindex="1"
                        placeholder="0"
                        v-model="standardRatePrice"
                        :min="0"
                        :error="errors.standard_rate_price"
                    />

                    <CurrencySelect
                        id="standard_rate_currency"
                        label="Currency"
                        :tabindex="2"
                        v-model="standardRateCurrency"
                        :error="errors.standard_rate_currency"
                    />

                    <FormField
                        id="standard_rate_price_type"
                        label="Price Type"
                        type="text"
                        :tabindex="3"
                        autocomplete="off"
                        placeholder="e.g. nightly, person, hourly"
                        v-model="standardRatePriceType"
                        :error="errors.standard_rate_price_type"
                        help-text="How this price is calculated: nightly, person, hourly, daily, session, flat, etc."
                    />
                </TabsContent>

                <!-- Selling Points Tab -->
                <TabsContent value="selling-points" class="space-y-4 mt-4">
                    <SellingPointsEditor
                        v-model="sellingPoints"
                        :error="errors.selling_points"
                        :tabindex="1"
                    />
                </TabsContent>

                <!-- Location Tab -->
                <TabsContent value="location" class="space-y-4 mt-4">
                    <FormField
                        id="location_address"
                        label="Address"
                        type="text"
                        :tabindex="1"
                        autocomplete="off"
                        placeholder="e.g. Songan A, Kintamani, Bangli"
                        v-model="locationAddress"
                        :error="errors['location.address']"
                        :optional="true"
                    />

                    <FormField
                        id="location_subtitle"
                        label="Subtitle / Distance"
                        type="text"
                        :tabindex="2"
                        autocomplete="off"
                        placeholder="e.g. 2 hours from Ngurah Rai Airport"
                        v-model="locationSubtitle"
                        :error="errors['location.subtitle']"
                        :optional="true"
                    />

                    <FormField
                        id="location_map_url"
                        label="Map URL"
                        type="text"
                        :tabindex="3"
                        autocomplete="off"
                        placeholder="e.g. https://www.google.com/maps/..."
                        v-model="locationMapUrl"
                        :error="errors['location.map_url']"
                        :optional="true"
                        help-text="Google Maps, Apple Maps, Waze, or any map link"
                    />

                    <LocationHighlightsEditor
                        v-model="locationHighlights"
                        :error="errors['location.highlights']"
                    />
                </TabsContent>

                <!-- Media Tab -->
                <TabsContent value="media" class="space-y-4 mt-4">
                    <ImageUploader
                        label="Images"
                        name="images"
                        :multiple="true"
                        :max-files="25"
                        :error="errors.images || errors.uploaded_media_ids"
                        :tabindex="1"
                        :disabled="processing"
                        @update:uploaded-media-ids="uploadedMediaIds = $event"
                    />
                </TabsContent>
            </Tabs>

            <SubmitButton
                :processing="processing"
                :tabindex="99"
                test-id="create-unit-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>