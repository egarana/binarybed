<script setup lang="ts">
import TenantLayout from '@/layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';
import tenantRoutes from '@/routes/tenant';
import { nested } from '@/routes/tenant/page';
</script>

<template>
    <TenantLayout v-slot="{ tenant }" :title="'Dining'">
        <div class="flex flex-col items-center justify-center min-h-dvh p-8">
            <h1 class="text-3xl font-bold mb-4">Dining</h1>
            <h2 class="text-xl font-medium">{{ tenant.name }}</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-8">{{ tenant.domain }}</p>
            
            <nav class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-w-4xl">
                <Link 
                    :href="tenantRoutes.home()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    Home
                </Link>
                
                <Link 
                    :href="tenantRoutes.about()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    About Us
                </Link>
                
                <Link 
                    :href="tenantRoutes.contact()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    Contact Us
                </Link>
                
                <Link 
                    :href="tenantRoutes.faq()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    FAQ
                </Link>
                
                <Link 
                    :href="tenantRoutes.privacyPolicy()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    Privacy Policy
                </Link>
                
                <Link 
                    :href="tenantRoutes.cancellationPolicy()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    Cancellation Policy
                </Link>
                
                <Link 
                    :href="tenantRoutes.termsAndConditions()" 
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-center transition-colors"
                >
                    Terms & Conditions
                </Link>
                
                <Link 
                    :href="tenantRoutes.page('dining')" 
                    class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg text-center transition-colors"
                >
                    Dining
                </Link>

                <Link 
                    :href="nested({ parent: 'dining', child: 'bali-tower-bistro' })" 
                    class="lg:col-start-1 px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-center transition-colors"
                >
                    Bali Tower Bistro
                </Link>

                <Link 
                    :href="nested({ parent: 'dining', child: 'pantai-restaurant' })" 
                    class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-center transition-colors"
                >
                    Pantai Restaurant
                </Link>

                <Link 
                    :href="nested({ parent: 'dining', child: 'kijang-restaurant' })" 
                    class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-center transition-colors"
                >
                    Kijang Restaurant
                </Link>
                
                <Link 
                    :href="tenantRoutes.page('rooms')" 
                    class="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-center transition-colors"
                >
                    Rooms
                </Link>
                
                <Link 
                    :href="tenantRoutes.page('experiences')" 
                    class="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-center transition-colors"
                >
                    Experiences
                </Link>
            </nav>
        </div>
    </TenantLayout>
</template>
