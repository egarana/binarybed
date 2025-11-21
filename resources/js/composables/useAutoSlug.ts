import { ref, watch, type Ref } from 'vue';
import { slugify as translitSlug } from 'transliteration';

interface UseAutoSlugOptions {
    /** Custom separator for slug words. Default: '-' */
    separator?: string;
    /** Whether to convert to lowercase. Default: true */
    lowercase?: boolean;
    /** Initial value for slug */
    initialValue?: string;
}

export function useAutoSlug(source: Ref<string>, options: UseAutoSlugOptions = {}) {
    const { separator = '-', lowercase = true, initialValue = '' } = options;

    const slug = ref(initialValue);
    const previousAutoSlug = ref('');

    // Set previous auto slug based on initial value if provided
    if (initialValue) {
        previousAutoSlug.value = initialValue;
    }

    watch(source, (newVal) => {
        if (!newVal || newVal.trim() === '') {
            slug.value = '';
            previousAutoSlug.value = '';
            return;
        }

        const currentAuto = translitSlug(newVal, {
            lowercase,
            separator,
        });

        // Only auto-generate if slug is empty or still matches previous auto value
        // This allows manual editing while preserving auto-generation behavior
        if (!slug.value || slug.value === previousAutoSlug.value) {
            slug.value = currentAuto;
            previousAutoSlug.value = currentAuto;
        }
    });

    return { slug };
}