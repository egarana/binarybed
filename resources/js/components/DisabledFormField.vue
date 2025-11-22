<script setup lang="ts">
interface Props {
    label: string;
    value: string | number;
    helpText?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="grid gap-2">
        <h1 class="disabled-label">{{ label }}</h1>
        <div class="disabled-input">
            {{ value }}
        </div>
        <p v-if="helpText" class="text-xs text-muted-foreground">
            {{ helpText }}
        </p>
    </div>
</template>
