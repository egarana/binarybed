<script setup lang="ts">
import rates from '@/routes/rates';
import { ref, onMounted, computed } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import NumberFormField from '@/components/NumberFormField.vue';
import CurrencyFormField from '@/components/CurrencyFormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Item, ItemContent, ItemTitle, ItemDescription, ItemActions } from '@/components/ui/item';
import DisabledFormField from '@/components/DisabledFormField.vue';
import InputError from '@/components/InputError.vue';

interface ResourceOption extends ComboboxOption {
    type: string;
    id: number;
    tenant_id: string;
    tenant_name: string;
    resource_name: string;
}

const props = defineProps<{
    resources?: ResourceOption[];
}>();

// Get query params from URL for resource attachment
const urlParams = new URLSearchParams(window.location.search);
const resourceTypeFromUrl = urlParams.get('resource_type');
const resourceIdFromUrl = urlParams.get('resource_id');
const tenantIdFromUrl = urlParams.get('tenant_id');

const isResourceAttachment = Boolean(resourceTypeFromUrl && resourceIdFromUrl);

const breadcrumbs = [
    { title: 'Rates', href: rates.index.url() },
    { title: 'Create Rate', href: rates.create.url() },
];

const { onSuccess, onError } = useFormNotifications({
    resourceName: 'rate',
    action: 'create',
});

// Form fields
const selectedResource = ref<ResourceOption>();

// Pre-select resource if from resource context
onMounted(() => {
    if (isResourceAttachment && props.resources) {
        const valueToFind = `${resourceTypeFromUrl}:${tenantIdFromUrl}:${resourceIdFromUrl}`;
        const foundResource = props.resources.find(r => r.value === valueToFind);
        if (foundResource) {
            selectedResource.value = foundResource;
        }
    }
});

const name = ref('');
const { slug } = useAutoSlug(name, {
    separator: '-',
    lowercase: true
});

const description = ref('');
const price = ref(0);
const currency = ref('IDR');
const isActive = ref(true);

// Computed values derived from selected resource
const tenantId = computed(() => {
    if (tenantIdFromUrl) return tenantIdFromUrl;
    return selectedResource.value?.tenant_id ?? '';
});

const rateableType = computed(() => {
    if (resourceTypeFromUrl) {
        return `App\\Models\\${resourceTypeFromUrl}`;
    }
    if (selectedResource.value) {
        return `App\\Models\\${selectedResource.value.type}`;
    }
    return '';
});

const rateableId = computed(() => {
    if (resourceIdFromUrl) {
        return resourceIdFromUrl;
    }
    if (selectedResource.value) {
        return selectedResource.value.id;
    }
    return '';
});

// Transform form data before submission
const transformData = (data: Record<string, any>) => ({
    ...data,
    tenant_id: tenantId.value,
    rateable_type: rateableType.value,
    rateable_id: rateableId.value,
    redirect_to_resource: isResourceAttachment ? '1' : '0',
    is_active: isActive.value ? '1' : '0',
});
</script>

<template>
    <BaseFormPage
        title="Create Rate"
        :breadcrumbs="breadcrumbs"
        :action="rates.store.url()"
        method="post"
        :transform="transformData"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">

            <!-- Show resource info if attaching from resource page -->
            <DisabledFormField
                v-if="isResourceAttachment"
                label="Attaching to"
                :value="`${resourceTypeFromUrl} #${resourceIdFromUrl}`"
                help-text="This rate will be created for this resource"
            />

            <!-- Unified Product Search -->
            <SearchableSelect
                v-if="!isResourceAttachment"
                mode="single"
                v-model="selectedResource"
                :options="resources"
                :fetch-url="() => rates.create.url()"
                response-key="resources"
                label="Product"
                placeholder="Search by product name or tenant..."
                search-placeholder="Type to search..."
                :tabindex="1"
                :error="errors.rateable_id || errors.tenant_id"
                :required="true"
                :clearable="true"
                :disabled="processing"
            />

            <!-- Active Switch -->
            <Item variant="outline">
                <ItemContent>
                    <ItemTitle>Active</ItemTitle>
                    <ItemDescription>When enabled, this rate will be visible and can be applied to bookings. Disable to temporarily hide without deleting.</ItemDescription>
                </ItemContent>
                <ItemActions>
                    <Switch id="is_active" v-model="isActive" :default-checked="true" />
                </ItemActions>
            </Item>

            <FormField
                id="name"
                label="Name"
                type="text"
                :tabindex="2"
                autocomplete="organization"
                placeholder="e.g. Daily Rate"
                v-model="name"
                :error="errors.name"
            />

            <FormField
                id="slug"
                label="Slug"
                type="text"
                :tabindex="3"
                autocomplete="off"
                placeholder="e.g. daily-rate"
                v-model="slug"
                :error="errors.slug"
            />

            <!-- Description -->
            <div class="grid gap-2">
                <Label for="description">Description (Optional)</Label>
                <Textarea
                    id="description"
                    name="description"
                    :tabindex="4"
                    placeholder="Describe this rate plan, including what's included, terms, or special conditions..."
                    v-model="description"
                    rows="6"
                />
                <InputError :message="errors.description" />
            </div>

            <NumberFormField
                id="price"
                label="Price"
                :tabindex="5"
                placeholder="0"
                v-model="price"
                :min="0"
                :error="errors.price"
            />

            <!-- Currency -->
            <CurrencyFormField
                id="currency"
                label="Currency"
                :tabindex="6"
                placeholder="IDR"
                v-model="currency"
                :error="errors.currency"
            />

            <SubmitButton
                :processing="processing"
                :tabindex="7"
                test-id="create-rate-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>
