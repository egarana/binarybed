<script setup lang="ts">
import { ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { X } from 'lucide-vue-next';
import FormField from '@/components/FormField.vue';

interface Host {
    name: string;
    photo?: string;
    languages?: string[];
    story?: string;
    whatsapp?: string;
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
const hostWhatsapp = ref(props.modelValue?.whatsapp || '');
const hostInstagram = ref(props.modelValue?.instagram || '');
const hostFacebook = ref(props.modelValue?.facebook || '');
const hostTiktok = ref(props.modelValue?.tiktok || '');
const hostLanguages = ref<string[]>(props.modelValue?.languages || []);
const newLanguage = ref('');

// Language management
const addLanguage = () => {
    const lang = newLanguage.value.trim();
    if (lang && !hostLanguages.value.includes(lang) && hostLanguages.value.length < 5) {
        hostLanguages.value.push(lang);
        newLanguage.value = '';
        emitUpdate();
    }
};

const removeLanguage = (index: number) => {
    hostLanguages.value.splice(index, 1);
    emitUpdate();
};

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
        whatsapp: hostWhatsapp.value.trim() || undefined,
        instagram: hostInstagram.value.trim() || undefined,
        facebook: hostFacebook.value.trim() || undefined,
        tiktok: hostTiktok.value.trim() || undefined,
    };

    emit('update:modelValue', host);
};

// Watch individual fields
watch([hostName, hostPhoto, hostStory, hostWhatsapp, hostInstagram, hostFacebook, hostTiktok], () => {
    emitUpdate();
});

// Watch for external changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            hostName.value = newValue.name || '';
            hostPhoto.value = newValue.photo || '';
            hostStory.value = newValue.story || '';
            hostWhatsapp.value = newValue.whatsapp || '';
            hostInstagram.value = newValue.instagram || '';
            hostFacebook.value = newValue.facebook || '';
            hostTiktok.value = newValue.tiktok || '';
            hostLanguages.value = newValue.languages || [];
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
        <div class="grid gap-2">
            <Label class="flex items-center gap-1">
                Languages
                <span class="text-muted-foreground">(Optional, max 5)</span>
            </Label>
            <div class="flex gap-2">
                <Input
                    v-model="newLanguage"
                    placeholder="e.g., English"
                    type="text"
                    @keydown.enter.prevent="addLanguage"
                    :disabled="hostLanguages.length >= 5"
                />
                <Button
                    @click="addLanguage"
                    type="button"
                    variant="secondary"
                    :disabled="!newLanguage.trim() || hostLanguages.length >= 5"
                >
                    Add
                </Button>
            </div>
            <div v-if="hostLanguages.length > 0" class="flex flex-wrap gap-2">
                <Badge
                    v-for="(lang, index) in hostLanguages"
                    :key="index"
                    variant="secondary"
                    class="flex items-center gap-1"
                >
                    {{ lang }}
                    <button
                        @click="removeLanguage(index)"
                        type="button"
                        class="hover:bg-muted rounded-sm p-0.5"
                    >
                        <X class="h-3 w-3" />
                    </button>
                </Badge>
            </div>
        </div>

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
        <FormField
            id="host_whatsapp"
            label="WhatsApp Number"
            type="tel"
            v-model="hostWhatsapp"
            placeholder="+62812345678"
            :optional="true"
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
