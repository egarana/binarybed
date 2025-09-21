<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

// Tailwind's lg breakpoint is 1024px
// We'll treat <1024px (sm + md) as "mobile"
const isMobile = ref(false)
const isReady = ref(false)

function updateMedia(e: MediaQueryList | MediaQueryListEvent) {
    // If >=1024px => desktop, else mobile
    const matches = 'matches' in e ? e.matches : (e as MediaQueryList).matches
    isMobile.value = !matches
}

let media: MediaQueryList

onMounted(() => {
    media = window.matchMedia('(min-width: 1024px)')
    updateMedia(media)

    media.addEventListener?.('change', updateMedia)
    media.addListener?.(updateMedia) // Safari fallback

    isReady.value = true
})

onUnmounted(() => {
    media?.removeEventListener?.('change', updateMedia)
    media?.removeListener?.(updateMedia)
})
</script>

<template>
    <!-- Targets -->
    <div id="desktop-target" class="hidden lg:block border p-4 mt-6">
        <p class="text-gray-600">Desktop Target (≥1024px)</p>
    </div>

    <div id="mobile-target" class="block lg:hidden border p-4 mt-6">
        <p class="text-gray-600">Mobile Target (&lt;1024px → sm + md)</p>
    </div>

    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Responsive Teleport</h1>

        <!-- Teleport -->
        <teleport v-if="isReady" :to="isMobile ? '#mobile-target' : '#desktop-target'">
            <div class="bg-blue-500 text-white p-3 rounded shadow">
                Navigation Menu (teleports instantly, no flicker 🚀)
            </div>
        </teleport>
    </div>
</template>
