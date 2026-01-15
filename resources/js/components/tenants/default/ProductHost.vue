<script setup lang="ts">
import { computed } from 'vue';
import { type Resource } from '@/stores/useResourceStore';
import { MessageCircle } from 'lucide-vue-next';

interface Props {
    resource: Resource;
    resourceType?: 'units' | 'activities';
}

const props = defineProps<Props>();

const host = computed(() => props.resource.host);
const title = computed(() => props.resourceType === 'activities' ? 'Meet your guide' : 'Meet your host');
</script>

<template>
    <div v-if="host" class="mx-6 py-6 border-t md:mx-0 md:py-8">
        <h1 class="text-lg font-semibold mb-4">{{ title }}</h1>
        
        <div class="flex items-start gap-4">
            <img
                v-if="host.photo"
                :src="host.photo"
                :alt="host.name"
                class="w-16 h-16 rounded-full object-cover"
            />
            <div v-else class="w-16 h-16 rounded-full bg-stone-200 flex items-center justify-center text-2xl font-semibold text-stone-600">
                {{ host.name.charAt(0).toUpperCase() }}
            </div>
            
            <div class="flex-1">
                <p class="font-semibold text-stone-900 mb-1">{{ host.name }}</p>
                
                <p v-if="host.languages && host.languages.length > 0" class="text-sm text-stone-500 mb-3">
                    {{ host.languages.join(', ') }}
                </p>
                
                <p v-if="host.story" class="text-stone-600 text-sm leading-relaxed mb-4">
                    "{{ host.story }}"
                </p>
                
                <a
                    v-if="host.whatsapp"
                    :href="`https://wa.me/${host.whatsapp.replace(/[^0-9]/g, '')}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm"
                >
                    <MessageCircle class="w-4 h-4" />
                    Chat on WhatsApp
                </a>

                <!-- Social Media Links -->
                <div v-if="host.instagram || host.facebook || host.tiktok" class="flex items-center gap-3 mt-3">
                    <a
                        v-if="host.instagram"
                        :href="host.instagram.startsWith('http') ? host.instagram : `https://instagram.com/${host.instagram.replace('@', '')}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-pink-600 transition-colors"
                        title="Instagram"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a
                        v-if="host.facebook"
                        :href="host.facebook.startsWith('http') ? host.facebook : `https://facebook.com/${host.facebook}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-blue-600 transition-colors"
                        title="Facebook"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a
                        v-if="host.tiktok"
                        :href="host.tiktok.startsWith('http') ? host.tiktok : `https://tiktok.com/@${host.tiktok.replace('@', '')}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-black transition-colors"
                        title="TikTok"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
