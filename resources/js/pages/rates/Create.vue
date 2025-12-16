<script setup lang="ts">
import rates from '@/routes/rates';
import { ref, onMounted } from 'vue';

import { useFormNotifications } from '@/composables/useFormNotifications';
import { useAutoSlug } from '@/composables/useAutoSlug';
import BaseFormPage from '@/components/BaseFormPage.vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';
import FormField from '@/components/FormField.vue';
import SubmitButton from '@/components/SubmitButton.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import DisabledFormField from '@/components/DisabledFormField.vue';

const props = defineProps<{
    tenants?: ComboboxOption[];
    resourceType?: string;
    resourceId?: number;
    resourceName?: string;
}>();

// Get query params from URL for resource attachment
const urlParams = new URLSearchParams(window.location.search);
const resourceTypeFromUrl = urlParams.get('resource_type') || props.resourceType;
const resourceIdFromUrl = urlParams.get('resource_id') || props.resourceId;
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
const selectedTenant = ref<ComboboxOption>();

// Pre-select tenant if from resource context
onMounted(() => {
    if (tenantIdFromUrl && props.tenants) {
        const foundTenant = props.tenants.find(t => t.value === tenantIdFromUrl);
        if (foundTenant) {
            selectedTenant.value = foundTenant;
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

// Resource type mapping for display
const resourceTypeDisplay: Record<string, string> = {
    'Unit': 'Unit',
    'Activity': 'Activity',
};
</script>

<template>
    <BaseFormPage
        title="Create Rate"
        :breadcrumbs="breadcrumbs"
        :action="rates.store.url()"
        method="post"
        :onSuccess="onSuccess"
        :onError="onError"
    >
        <template #default="{ errors, processing }">
            <!-- Hidden fields for resource attachment -->
            <input 
                v-if="resourceTypeFromUrl" 
                type="hidden" 
                name="rateable_type" 
                :value="`App\\Models\\${resourceTypeFromUrl}`" 
            />
            <input 
                v-if="resourceIdFromUrl" 
                type="hidden" 
                name="rateable_id" 
                :value="resourceIdFromUrl" 
            />

            <!-- Show resource info if attaching to a resource -->
            <DisabledFormField
                v-if="isResourceAttachment"
                label="Attaching to"
                :value="`${resourceTypeDisplay[resourceTypeFromUrl!] || resourceTypeFromUrl} #${resourceIdFromUrl}`"
                help-text="This rate will be created for this resource"
            />

            <!-- Tenant Selection -->
            <SearchableSelect
                mode="single"
                v-model="selectedTenant"
                :options="tenants"
                :fetch-url="() => rates.create.url()"
                response-key="tenants"
                label="Tenant"
                placeholder="Select a tenant"
                search-placeholder="Search tenant..."
                name="tenant_id"
                :tabindex="1"
                :error="errors.tenant_id"
                :required="true"
                :clearable="true"
                :disabled="processing || Boolean(tenantIdFromUrl)"
            />

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

            <FormField
                id="description"
                label="Description"
                type="textarea"
                :tabindex="4"
                placeholder="Rate description (optional)"
                v-model="description"
                :error="errors.description"
            />

            <div class="grid grid-cols-2 gap-4">
                <FormField
                    id="price"
                    label="Price"
                    type="number"
                    :tabindex="5"
                    placeholder="0"
                    v-model="price"
                    :error="errors.price"
                />

                <FormField
                    id="currency"
                    label="Currency"
                    type="text"
                    :tabindex="6"
                    placeholder="IDR"
                    v-model="currency"
                    :error="errors.currency"
                />
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox id="is_active" v-model:checked="isActive" name="is_active" />
                <Label for="is_active" class="cursor-pointer">Active</Label>
            </div>

            <SubmitButton
                :processing="processing"
                :tabindex="7"
                test-id="create-rate-button"
                label="Create"
            />
        </template>
    </BaseFormPage>
</template>
