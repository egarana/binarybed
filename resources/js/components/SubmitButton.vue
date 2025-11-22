<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    processing: boolean;
    tabindex?: number;
    testId?: string;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Submit',
});
</script>

<template>
    <div class="mt-auto text-right pt-6">
        <Button
            type="submit"
            :tabindex="tabindex"
            :disabled="processing"
            :data-test="testId"
        >
            <LoaderCircle
                v-if="processing"
                class="h-4 w-4 animate-spin"
            />
            {{ label }}
        </Button>
    </div>
</template>
