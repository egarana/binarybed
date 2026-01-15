<script setup lang="ts">
import { ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import FormField from '@/components/FormField.vue';
import PhoneInput from '@/components/PhoneInput.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';

interface PhoneData {
    country: {
        country: string;
        countryName: string;
        code: string;
    };
    number: string;
}

interface Host {
    name: string;
    photo?: string;
    languages?: string[];
    story?: string;
    whatsapp?: PhoneData;
    instagram?: string;
    facebook?: string;
    tiktok?: string;
}

interface Props {
    modelValue?: Host | null;
    error?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: Host | null): void;
}>();

// Internal state
const hostName = ref(props.modelValue?.name || '');
const hostPhoto = ref(props.modelValue?.photo || '');
const hostStory = ref(props.modelValue?.story || '');
const hostWhatsapp = ref<PhoneData | null>(props.modelValue?.whatsapp || null);
const hostInstagram = ref(props.modelValue?.instagram || '');
const hostFacebook = ref(props.modelValue?.facebook || '');
const hostTiktok = ref(props.modelValue?.tiktok || '');
const hostLanguages = ref<string[]>(props.modelValue?.languages || []);

// Emit updates
const emitUpdate = () => {
    // If name is empty, emit null (no host data)
    if (!hostName.value.trim()) {
        emit('update:modelValue', null);
        return;
    }

    // Otherwise emit host object
    const host: Host = {
        name: hostName.value.trim(),
        photo: hostPhoto.value.trim() || undefined,
        languages: hostLanguages.value.length > 0 ? hostLanguages.value : undefined,
        story: hostStory.value.trim() || undefined,
        whatsapp: hostWhatsapp.value || undefined,
        instagram: hostInstagram.value.trim() || undefined,
        facebook: hostFacebook.value.trim() || undefined,
        tiktok: hostTiktok.value.trim() || undefined,
    };

    emit('update:modelValue', host);
};

// Watch all fields together (including languages)
watch([hostName, hostPhoto, hostStory, hostWhatsapp, hostInstagram, hostFacebook, hostTiktok, hostLanguages], () => {
    emitUpdate();
}, { deep: true });

// Watch for external changes (from parent component)
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            // Only update if values actually changed to prevent infinite loops
            if (newValue.name !== hostName.value) hostName.value = newValue.name || '';
            if (newValue.photo !== hostPhoto.value) hostPhoto.value = newValue.photo || '';
            if (newValue.story !== hostStory.value) hostStory.value = newValue.story || '';
            if (JSON.stringify(newValue.whatsapp) !== JSON.stringify(hostWhatsapp.value)) {
                hostWhatsapp.value = newValue.whatsapp || null;
            }
            if (newValue.instagram !== hostInstagram.value) hostInstagram.value = newValue.instagram || '';
            if (newValue.facebook !== hostFacebook.value) hostFacebook.value = newValue.facebook || '';
            if (newValue.tiktok !== hostTiktok.value) hostTiktok.value = newValue.tiktok || '';
            
            // For languages, compare arrays
            const newLangs = newValue.languages || [];
            if (JSON.stringify(newLangs) !== JSON.stringify(hostLanguages.value)) {
                hostLanguages.value = newLangs;
            }
        }
    },
    { deep: true }
);

</script>

<template>
    <div class="grid gap-4">
        <!-- Name -->
        <FormField
            id="host_name"
            label="Name"
            v-model="hostName"
            placeholder="e.g., Made Wirawan"
            :error="error"
        />

        <!-- Photo URL -->
        <FormField
            id="host_photo"
            label="Photo URL"
            type="url"
            v-model="hostPhoto"
            placeholder="https://example.com/photo.jpg"
            :optional="true"
        />

        <!-- Languages -->
        <LanguageSelect v-model="hostLanguages" />

        <!-- Story -->
        <div class="grid gap-2">
            <div class="flex items-center justify-between">
                <Label for="host_story" class="flex items-center gap-1">
                    Story
                    <span class="text-muted-foreground">(Optional, max 1000 characters)</span>
                </Label>
                <p class="text-xs text-muted-foreground">
                    {{ hostStory.length }}/1000
                </p>
            </div>
            <Textarea
                id="host_story"
                name="host_story"
                placeholder="Tell guests about yourself and your property..."
                v-model="hostStory"
                rows="6"
                :maxlength="1000"
            />
        </div>

        <!-- WhatsApp -->
        <PhoneInput
            name="host_whatsapp"
            label="WhatsApp Number"
            v-model="hostWhatsapp"
            placeholder="81234567890"
        />

        <!-- Instagram -->
        <FormField
            id="host_instagram"
            label="Instagram"
            v-model="hostInstagram"
            placeholder="@username or profile URL"
            :optional="true"
        />

        <!-- Facebook -->
        <FormField
            id="host_facebook"
            label="Facebook"
            v-model="hostFacebook"
            placeholder="Profile URL"
            :optional="true"
        />

        <!-- TikTok -->
        <FormField
            id="host_tiktok"
            label="TikTok"
            v-model="hostTiktok"
            placeholder="@username or profile URL"
            :optional="true"
        />
    </div>
</template>
