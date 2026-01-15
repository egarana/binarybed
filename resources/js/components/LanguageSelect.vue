<script setup lang="ts">
import { ref, watch } from 'vue';
import SearchableSelect, { type ComboboxOption } from '@/components/SearchableSelect.vue';

interface Props {
    modelValue?: string[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

// Language options (common languages for tourist destinations)
const languageOptions: ComboboxOption[] = [
    { value: 'English', label: 'English' },
    { value: 'Indonesian', label: 'Indonesian (Bahasa Indonesia)' },
    { value: 'Mandarin', label: 'Mandarin (中文)' },
    { value: 'Japanese', label: 'Japanese (日本語)' },
    { value: 'Korean', label: 'Korean (한국어)' },
    { value: 'French', label: 'French (Français)' },
    { value: 'German', label: 'German (Deutsch)' },
    { value: 'Spanish', label: 'Spanish (Español)' },
    { value: 'Italian', label: 'Italian (Italiano)' },
    { value: 'Russian', label: 'Russian (Русский)' },
    { value: 'Arabic', label: 'Arabic (العربية)' },
    { value: 'Dutch', label: 'Dutch (Nederlands)' },
    { value: 'Portuguese', label: 'Portuguese (Português)' },
    { value: 'Thai', label: 'Thai (ไทย)' },
    { value: 'Vietnamese', label: 'Vietnamese (Tiếng Việt)' },
];

// Local state for selected languages
const selectedLanguages = ref<ComboboxOption[]>([]);

// Initialize from props
const initializeFromProps = (languages: string[] | undefined) => {
    if (!languages || languages.length === 0) {
        selectedLanguages.value = [];
        return;
    }
    
    selectedLanguages.value = languages.map(lang => {
        const found = languageOptions.find(opt => opt.value === lang);
        return found || { value: lang, label: lang };
    });
};

// Initialize on mount
initializeFromProps(props.modelValue);

// Watch local state changes and emit to parent
watch(selectedLanguages, (newVal) => {
    const languages = newVal.map(opt => opt.value);
    // Only emit if actually different
    if (JSON.stringify(languages) !== JSON.stringify(props.modelValue || [])) {
        emit('update:modelValue', languages);
    }
}, { deep: true });

// Watch for external changes from parent
watch(() => props.modelValue, (newVal) => {
    const currentValues = selectedLanguages.value.map(opt => opt.value);
    // Only update if actually different
    if (JSON.stringify(newVal || []) !== JSON.stringify(currentValues)) {
        initializeFromProps(newVal);
    }
}, { deep: true });

</script>

<template>
    <SearchableSelect
        mode="multiple"
        label="Languages"
        placeholder="Select languages"
        search-placeholder="Search languages..."
        v-model="selectedLanguages"
        :options="languageOptions"
        :draggable="true"
        :required="false"
    />
</template>
