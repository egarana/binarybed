<script setup lang="ts">
import TenantLayout from '@/layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';
import { nested } from '@/routes/tenant/page';

interface Resource {
    id: number;
    name: string;
    slug: string;
    created_at: string;
}

interface Props {
    resources: Resource[];
    resourceType: 'units' | 'activities';
    resourceSlug: string;
}

defineProps<Props>();
</script>

<template>
    <TenantLayout v-slot="{ tenant }" :title="'Villas'">
        <div class="min-h-dvh p-8">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-3xl font-bold mb-2">Our Private Villas</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Exclusive villa retreats at {{ tenant.name }}
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="item in resources" 
                        :key="item.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
                    >
                        <div class="h-48 bg-gradient-to-br from-rose-400 to-pink-500 flex items-center justify-center">
                            <span class="text-white text-4xl">🏡</span>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">{{ item.name }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                                Retreat to our {{ item.name.toLowerCase() }}
                            </p>
                            <Link 
                                :href="nested({ parent: resourceSlug, child: item.slug })"
                                class="inline-block px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-sm transition-colors"
                            >
                                View Details →
                            </Link>
                        </div>
                    </div>
                </div>
                
                <div v-if="resources.length === 0" class="text-center py-16">
                    <p class="text-gray-500">No villas available at this time.</p>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
