<script setup lang="ts">
import { computed } from 'vue';
import { type Resource } from '@/stores/useResourceStore';
import { Button } from '@/components/ui/button';
import { Facebook, Instagram } from 'lucide-vue-next';

interface Props {
    resource: Resource;
    resourceType?: 'units' | 'activities';
}

const props = defineProps<Props>();

const host = computed(() => props.resource.host);
const title = computed(() => props.resourceType === 'activities' ? 'Meet your guide' : 'Meet your host');
</script>

<template>
    <div v-if="host" class="mx-6 pt-6 pb-10 border-t md:mx-0 md:pt-8 md:pb-12">
        <h1 class="text-lg font-semibold">{{ title }}</h1>

        <div class="mt-6">
            <div class="flex items-center gap-4">
                <div>
                    <img
                        v-if="host.photo"
                        :src="host.photo"
                        :alt="host.name"
                        class="w-16 h-16 rounded-full object-cover"
                    />
                    <div v-else class="w-16 h-16 rounded-full bg-accent flex items-center justify-center text-2xl font-semibold text-muted-foreground">
                        {{ host.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
                <div>
                    <h1 class="font-medium">{{ host.name }}</h1>
                    <p v-if="host.languages && host.languages.length > 0" class="text-sm text-muted-foreground">{{ host.languages.join(', ') }}</p>
                </div>
            </div>
            <div class="mt-4">
                <p v-if="host.story">
                    {{ host.story }}
                </p>
            </div>
            <div class="mt-6 flex items-center justify-between">
                <a
                    :href="`https://wa.me/${host.whatsapp.country.code.replace('+', '')}${host.whatsapp.number}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-block"
                >
                    <Button
                        v-if="host.whatsapp"
                        size="lg"
                        variant="outline"
                        class="cursor-pointer"
                    >
                        Chat on WhatsApp
                    </Button>
                </a>
                <div v-if="host.instagram || host.facebook || host.tiktok">
                    <ul class="flex items-center gap-2">
                        <li>
                            <a
                                v-if="host.instagram"
                                :href="host.instagram.startsWith('http') ? host.instagram : `https://instagram.com/${host.instagram.replace('@', '')}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="Instagram"
                            >
                                <Instagram class="size-5 stroke-1 text-muted-foreground hover:text-primary" />
                            </a>
                        </li>
                        <li>
                            <a
                                v-if="host.facebook"
                                :href="host.facebook.startsWith('http') ? host.facebook : `https://facebook.com/${host.facebook}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="Facebook"
                            >
                                <Facebook class="size-5 stroke-1 text-muted-foreground hover:text-primary" />
                            </a>
                        </li>
                        <li>
                            <a
                                v-if="host.tiktok"
                                :href="host.tiktok.startsWith('http') ? host.tiktok : `https://tiktok.com/@${host.tiktok.replace('@', '')}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="TikTok"
                            >
                                <svg class="size-[21px] fill-muted-foreground mt-[1px] hover:fill-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path d="M224,74a50.06,50.06,0,0,1-50-50,6,6,0,0,0-6-6H128a6,6,0,0,0-6,6V156a22,22,0,1,1-31.43-19.89A6,6,0,0,0,94,130.69V88a6,6,0,0,0-7-5.91C52.2,88.28,26,120.05,26,156a74,74,0,0,0,148,0V112.93A101.28,101.28,0,0,0,224,126a6,6,0,0,0,6-6V80A6,6,0,0,0,224,74Zm-6,39.8a89.13,89.13,0,0,1-46.5-16.69A6,6,0,0,0,162,102v54a62,62,0,0,1-124,0c0-27.72,18.47-52.48,44-60.38v31.53A34,34,0,1,0,134,156V30h28.29A62.09,62.09,0,0,0,218,85.71Z"></path></svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
