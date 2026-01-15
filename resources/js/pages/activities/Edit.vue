<script setup lang="ts">
import activities from '@/routes/activities';
import { ref } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import DisabledFormField from '@/components/DisabledFormField.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import ImageUploader, { type ExistingImage } from '@/components/ImageUploader.vue';
import HighlightsEditor from '@/components/HighlightsEditor.vue';
import SellingPointsEditor from '@/components/SellingPointsEditor.vue';
import BookingBenefitsEditor from '@/components/BookingBenefitsEditor.vue';
import RulesEditor from '@/components/RulesEditor.vue';
import HostEditor from '@/components/HostEditor.vue';
import LocationHighlightsEditor from '@/components/LocationHighlightsEditor.vue';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/vue3';
import { Item, ItemActions, ItemContent, ItemMedia, ItemTitle } from '@/components/ui/item';
import { ChevronRightIcon, ShieldAlert } from 'lucide-vue-next';

interface Props {
    activity: {
        id: number;
        tenant_id: string;
        tenant_name: string;
        name: string;
        slug: string;
        subtitle?: string;
        description?: string;
        highlights?: any[];
        selling_points?: any[];
        book_direct_benefits?: any[];
        host?: any;
        images?: ExistingImage[];
        location?: {
            address: string;
            subtitle?: string;
            map_url?: string;
            highlights?: string[];
        };
        rules?: any[];
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Activities', href: activities.index.url() },
    { title: 'Edit Activity', href: activities.edit.url([props.activity.tenant_id, props.activity.slug]) },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'activity',
    action: 'update',
});

const name = ref(props.activity.name || '');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true,
    initialValue: props.activity.slug || ''
});

const subtitle = ref(props.activity.subtitle || '');

// Image management
const existingImages = ref<ExistingImage[]>(props.activity.images || []);
const uploadedMediaIds = ref<number[]>([]);
const description = ref(props.activity.description || '');

// Initial state
const highlights = ref<any[]>(props.activity.highlights || []);
const sellingPoints = ref<any[]>(props.activity.selling_points || []);
const bookDirectBenefits = ref<any[]>(props.activity.book_direct_benefits || []);
const host = ref<any>(props.activity.host || null);
const locationAddress = ref(props.activity.location?.address || '');
const locationSubtitle = ref(props.activity.location?.subtitle || '');
const locationMapUrl = ref(props.activity.location?.map_url || '');
const locationHighlights = ref<string[]>(props.activity.location?.highlights || []);

// Rules (Activity Rules)
interface Rule {
    icon: string;
    label: string;
    _id?: string;
}

const rules = ref<Rule[]>(props.activity.rules || []);

// Transform function to prepare data for submission
function transformFormData(data: Record<string, any>) {
    const filteredHighlights = locationHighlights.value.filter(h => h?.trim());
    const hasLocationData = locationAddress.value || locationSubtitle.value || locationMapUrl.value || filteredHighlights.length > 0;

    const formData: Record<string, any> = {
        ...data,
        name: name.value,
        slug: slug.value,
        description: description.value,
        existing_images: existingImages.value.map(img => img.id),
        uploaded_media_ids: uploadedMediaIds.value,
        highlights: highlights.value
            .filter(h => h.label?.trim())
            .map(({ icon, label }) => ({ icon, label })),
        subtitle: subtitle.value,
        selling_points: sellingPoints.value
            .filter(sp => sp.title?.trim())
            .map(({ icon, title, description }) => ({ icon, title, description })),
        book_direct_benefits: bookDirectBenefits.value
            .filter(b => b.title?.trim())
            .map(({ icon, title, description }) => ({ icon, title, description })),
        // Host Information
        host: host.value,
        // Rules (Activity Rules)
        rules: rules.value
            .filter(r => r.label?.trim())
            .map(({ icon, label }) => ({ icon, label })),
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


// Error tracking for badge indicators
const hasGeneralErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.name) count++;
    if (errors?.slug) count++;
    if (errors?.subtitle) count++;
    if (errors?.description) count++;
    return count;
};

const hasHighlightsErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.highlights) count++;
    return count;
};

const hasPricingErrors = (_errors: Record<string, any>) => {
    void _errors; // Explicitly mark as intentionally unused
    return 0; // Edit page doesn't have pricing fields
};

const hasMediaErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.images) count++;
    if (errors?.uploaded_media_ids) count++;
    if (errors?.existing_images) count++;
    return count;
};

const hasSellingPointsErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.selling_points) count++;
    return count;
};

const hasBookingBenefitsErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.book_direct_benefits) count++;
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

const hasRulesErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.rules) count++;
    return count;
};

const hasHostErrors = (errors: Record<string, any>) => {
    let count = 0;
    if (errors?.host) count++;
    return count;
};
</script>

<template>
    <BaseFormPage
        :title="`Edit Activity: ${activity.name}`"
        :breadcrumbs="breadcrumbs"
        :action="activities.update.url([activity.tenant_id, activity.slug])"
        method="put"
        :onSuccess="onSuccess"
        :onError="onError"
        :transform="transformFormData"
    >
        <template #default="{ errors, processing }">
            <input type="hidden" name="tenant_id" :value="activity.tenant_id" />

            <Tabs default-value="general">
                <TabsList>
                    <TabsTrigger value="general">
                        General
                        <Badge v-if="hasGeneralErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasGeneralErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="highlights">
                        Highlights
                        <Badge v-if="hasHighlightsErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasHighlightsErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="pricing">
                        Pricing
                        <Badge v-if="hasPricingErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasPricingErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="selling-points">
                        Selling Points
                        <Badge v-if="hasSellingPointsErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasSellingPointsErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="booking-benefits">
                        Booking Benefits
                        <Badge v-if="hasBookingBenefitsErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasBookingBenefitsErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="location">
                        Location
                        <Badge v-if="hasLocationErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasLocationErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="host">
                        Host
                        <Badge v-if="hasHostErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasHostErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="rules">
                        Activity Rules
                        <Badge v-if="hasRulesErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasRulesErrors(errors) }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="media">
                        Media
                        <Badge v-if="hasMediaErrors(errors)" variant="destructive" class="ml-1.5 text-[11px] rounded-full p-0 h-5 w-5">{{ hasMediaErrors(errors) }}</Badge>
                    </TabsTrigger>
                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="space-y-4 mt-4">
                    <DisabledFormField
                        label="Tenant"
                        :value="activity.tenant_name"
                        help-text="Activity tenant cannot be changed after creation"
                    />

                    <FormField
                        id="name"
                        label="Name"
                        type="text"
                        :tabindex="1"
                        autocomplete="organization"
                        placeholder="e.g. Activity Name"
                        v-model="name"
                        :error="errors.name"
                    />

                    <FormField
                        id="slug"
                        label="Slug"
                        type="text"
                        :tabindex="2"
                        autocomplete="off"
                        placeholder="e.g. activity-name"
                        v-model="slug"
                        :error="errors.slug"
                    />

                    <FormField
                        id="subtitle"
                        label="Subtitle"
                        type="text"
                        :tabindex="3"
                        autocomplete="off"
                        placeholder="e.g. Guided Adventure"
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
                            :tabindex="4"
                            placeholder="Describe this activity..."
                            v-model="description"
                            rows="8"
                        />
                        <InputError :message="errors.description" />
                    </div>
                </TabsContent>

                <!-- Highlights Tab -->
                <TabsContent value="highlights" class="space-y-4 mt-4">
                    <HighlightsEditor
                        v-model="highlights"
                        :error="errors.highlights"
                        :tabindex="1"
                    />
                </TabsContent>

                <!-- Pricing Tab -->
                <TabsContent value="pricing" class="space-y-4 mt-4">
                    <Item variant="outline" size="sm" as-child>
                        <Link :href="activities.rates.url([activity.tenant_id, activity.slug])">
                            <ItemMedia>
                                <ShieldAlert class="size-5" />
                            </ItemMedia>
                            <ItemContent>
                                <ItemTitle>Pricing is configured separately in rate plans.</ItemTitle>
                            </ItemContent>
                            <ItemActions>
                                <ChevronRightIcon class="size-4" />
                            </ItemActions>
                        </Link>
                    </Item>
                </TabsContent>

                <!-- Selling Points Tab -->
                <TabsContent value="selling-points" class="space-y-4 mt-4">
                    <SellingPointsEditor
                        v-model="sellingPoints"
                        :error="errors.selling_points"
                        :tabindex="1"
                    />
                </TabsContent>

                <!-- Booking Benefits Tab -->
                <TabsContent value="booking-benefits" class="space-y-4 mt-4">
                    <BookingBenefitsEditor
                        v-model="bookDirectBenefits"
                        :error="errors.book_direct_benefits"
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

                <!-- Host Tab -->
                <TabsContent value="host" class="space-y-4 mt-4">
                    <HostEditor
                        v-model="host"
                        :error="errors.host"
                    />
                </TabsContent>

                <!-- Activity Rules Tab -->
                <TabsContent value="rules" class="space-y-4 mt-4">
                    <RulesEditor
                        v-model="rules"
                        :error="errors.rules"
                        label="Activity Rules"
                        description="Add rules or guidelines that participants must follow."
                        empty-title="No activity rules added"
                        empty-description="Add rules like arrival time, age requirements, or dress code."
                        :tabindex="1"
                    />
                </TabsContent>

                <!-- Media Tab -->
                <TabsContent value="media" class="space-y-4 mt-4">
                    <ImageUploader
                        :existing-images="existingImages"
                        @update:existing-images="existingImages = $event"
                        @update:uploaded-media-ids="uploadedMediaIds = $event"
                        label="Images"
                        name="images"
                        :multiple="true"
                        :max-files="25"
                        :error="errors.images || errors.uploaded_media_ids || errors.existing_images"
                        :tabindex="1"
                        :disabled="processing"
                    />
                </TabsContent>
            </Tabs>

            <SubmitButton
                :processing="processing"
                :tabindex="99"
                test-id="update-activity-button"
                label="Save"
            />
        </template>
    </BaseFormPage>
</template>