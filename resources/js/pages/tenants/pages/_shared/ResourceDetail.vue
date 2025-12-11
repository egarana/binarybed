<script setup lang="ts">
import TenantLayout from '@/layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';


interface Resource {
    id: number;
    name: string;
    slug: string;
    created_at: string;
}

interface Props {
    resource: Resource;
    resourceType: 'units' | 'activities';
    parentSlug: string;
}

// Generate URL for the parent page
const getParentUrl = (slug: string) => `/${slug}`;

const props = defineProps<Props>();

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Get appropriate icon and color based on resource type
const getIcon = () => {
    return props.resourceType === 'activities' ? '⛰️' : '🏠';
};

const getGradient = () => {
    return props.resourceType === 'activities' 
        ? 'from-green-400 to-emerald-600' 
        : 'from-blue-400 to-indigo-600';
};

const getButtonColor = () => {
    return props.resourceType === 'activities'
        ? 'bg-green-500 hover:bg-green-600'
        : 'bg-blue-500 hover:bg-blue-600';
};
</script>

<template>
    <TenantLayout v-slot="{ tenant }" :title="resource.name">
        <div class="min-h-dvh bg-gray-50 dark:bg-gray-900">
            <!-- Hero Section -->
            <div :class="`h-64 md:h-80 bg-gradient-to-br ${getGradient()} flex items-center justify-center relative`">
                <span class="text-8xl">{{ getIcon() }}</span>
                <div class="absolute inset-0 bg-black/20"></div>
            </div>
            
            <!-- Content -->
            <div class="max-w-4xl mx-auto px-6 -mt-16 relative z-10 pb-12">
                <!-- Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                    <!-- Breadcrumb -->
                    <nav class="mb-6">
                        <ol class="flex items-center space-x-2 text-sm">
                            <li>
                                <Link 
                                    :href="getParentUrl(parentSlug)" 
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    {{ parentSlug.charAt(0).toUpperCase() + parentSlug.slice(1) }}
                                </Link>
                            </li>
                            <li class="text-gray-400">/</li>
                            <li class="text-gray-900 dark:text-white font-medium">
                                {{ resource.name }}
                            </li>
                        </ol>
                    </nav>
                    
                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ resource.name }}
                    </h1>
                    
                    <!-- Meta -->
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400 mb-8">
                        <span class="flex items-center gap-1">
                            📅 Added {{ formatDate(resource.created_at) }}
                        </span>
                        <span class="flex items-center gap-1">
                            🏷️ {{ resourceType === 'activities' ? 'Activity' : 'Unit' }}
                        </span>
                    </div>
                    
                    <!-- Description Placeholder -->
                    <div class="prose dark:prose-invert max-w-none mb-8">
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Welcome to {{ resource.name }} at {{ tenant.name }}. 
                            This {{ resourceType === 'activities' ? 'activity' : 'accommodation' }} 
                            offers an unforgettable experience in the heart of Bali.
                        </p>
                        <p class="text-gray-500 dark:text-gray-400 italic mt-4">
                            [Additional details, pricing, and descriptions can be added to the database model]
                        </p>
                    </div>
                    
                    <!-- CTA -->
                    <div class="flex flex-wrap gap-4">
                        <button 
                            :class="`px-6 py-3 ${getButtonColor()} text-white rounded-lg font-medium transition-colors`"
                        >
                            {{ resourceType === 'activities' ? 'Book Now' : 'Reserve' }}
                        </button>
                        <Link 
                            :href="getParentUrl(parentSlug)"
                            class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            ← Back to {{ parentSlug }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
