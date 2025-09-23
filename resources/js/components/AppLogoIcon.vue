<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xml:space="preserve" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 118"><g paint-order="fill markers stroke" transform="matrix(3.7795 0 0 3.7795 -316 -267.754)"><rect width="31.221" height="31.221" x="83.608" y="70.843" rx="4.762" ry="4.762" fill="#ec131c"/><g transform="translate(-24.741 -2.813)" fill="#fff"><rect width="2.91" height="11.906" x="118.933" y="88.262" rx="1.455" ry="1.488"/><g transform="translate(-.688)"><rect width="2.91" height="11.906" x="92.759" y="-135.549" rx="1.455" ry="1.488" transform="rotate(90)"/><rect width="2.91" height="11.906" x="97.257" y="-135.549" rx="1.455" ry="1.488" transform="rotate(90)"/><rect width="2.91" height="7.355" x="123.643" y="92.812" rx="1.455" ry="1.455"/><rect width="2.91" height="7.355" x="132.638" y="92.812" rx="1.455" ry="1.455"/></g></g></g></svg>
</template>
