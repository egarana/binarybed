<script setup lang="ts">
/**
 * TenantDebugger - Development-only debugger for TenantLayout
 * 
 * Shows:
 * - Inertia page props (including tenant data)
 * - Pinia store states (tenant & resource stores)
 * - Data Storage Matrix (what should be cached vs fetched)
 * - Provided/injected values
 * - Performance metrics
 */
import { ref, computed, inject, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTenantStore } from '@/stores/useTenantStore';
import { useResourceStore } from '@/stores/useResourceStore';
import type { Tenant } from '@/types/tenant';

// Props
interface Props {
    enabled?: boolean;
    position?: 'bottom-right' | 'bottom-left' | 'top-right' | 'top-left';
}

const props = withDefaults(defineProps<Props>(), {
    enabled: true,
    position: 'bottom-right',
});

// State
const isExpanded = ref(false);
const activeTab = ref<'matrix' | 'inertia' | 'pinia' | 'inject' | 'performance'>('matrix');

// Stores
const tenantStore = useTenantStore();
const resourceStore = useResourceStore();

// Inertia page
const page = usePage();

// Inject tenant from parent (TenantLayout provides this)
const injectedTenant = inject<Tenant | null>('tenant', null);

// ===== DATA STORAGE MATRIX =====
interface DataMatrixItem {
    name: string;
    category: 'static' | 'semi-dynamic' | 'realtime' | 'sensitive';
    storePinia: boolean;
    serverFetch: 'on-load' | 'on-visit' | 'always' | 'never';
    cache: 'long' | 'short' | 'never' | 'session';
    cacheTime?: string;
    status: 'loaded' | 'not-loaded' | 'n/a';
    value: any;
    notes: string;
}

const dataStorageMatrix = computed<DataMatrixItem[]>(() => {
    const pageProps = page.props as any;
    
    return [
        // === STATIC DATA ===
        {
            name: 'Tenant Info',
            category: 'static',
            storePinia: true,
            serverFetch: 'on-load',
            cache: 'long',
            cacheTime: 'Session',
            status: tenantStore.isHydrated ? 'loaded' : pageProps.tenant ? 'not-loaded' : 'n/a',
            value: tenantStore.tenant || pageProps.tenant || null,
            notes: 'Hydrated from Inertia → Pinia'
        },
        {
            name: 'Resource Routes (config)',
            category: 'static',
            storePinia: true,
            serverFetch: 'on-load',
            cache: 'long',
            cacheTime: 'Session',
            status: Object.keys(tenantStore.resourceRoutes || {}).length > 0 ? 'loaded' : 'not-loaded',
            value: tenantStore.resourceRoutes || pageProps.tenant?.resource_routes || {},
            notes: 'Mapping slug → resource type'
        },
        {
            name: 'Units (basic list)',
            category: 'static',
            storePinia: true,
            serverFetch: 'on-load',
            cache: 'short',
            cacheTime: '5-10 min',
            status: resourceStore.units.length > 0 ? 'loaded' : 
                    pageProps.sharedResources?.units ? 'not-loaded' : 'n/a',
            value: resourceStore.units.length > 0 ? resourceStore.units : 
                   (pageProps.sharedResources?.units || []),
            notes: 'id, name, slug only - for navigation'
        },
        {
            name: 'Activities (basic list)',
            category: 'static',
            storePinia: true,
            serverFetch: 'on-load',
            cache: 'short',
            cacheTime: '5-10 min',
            status: resourceStore.activities.length > 0 ? 'loaded' : 
                    pageProps.sharedResources?.activities ? 'not-loaded' : 'n/a',
            value: resourceStore.activities.length > 0 ? resourceStore.activities : 
                   (pageProps.sharedResources?.activities || []),
            notes: 'id, name, slug only - for navigation'
        },
        
        // === SEMI-DYNAMIC DATA ===
        {
            name: 'Resource Detail (full)',
            category: 'semi-dynamic',
            storePinia: true,
            serverFetch: 'on-visit',
            cache: 'short',
            cacheTime: '5 min',
            status: pageProps.resource ? 'loaded' : 'n/a',
            value: pageProps.resource || null,
            notes: 'Cache by slug with expiry'
        },
        {
            name: 'Base Price',
            category: 'semi-dynamic',
            storePinia: true,
            serverFetch: 'on-visit',
            cache: 'short',
            cacheTime: '5 min',
            status: 'n/a',
            value: null,
            notes: 'Display only - re-validate on checkout'
        },
        {
            name: 'Features/Amenities',
            category: 'semi-dynamic',
            storePinia: true,
            serverFetch: 'on-visit',
            cache: 'short',
            cacheTime: '10 min',
            status: 'n/a',
            value: null,
            notes: 'Loaded with resource detail'
        },
        
        // === REAL-TIME DATA ===
        {
            name: 'Availability',
            category: 'realtime',
            storePinia: false,
            serverFetch: 'always',
            cache: 'never',
            status: 'n/a',
            value: null,
            notes: '⚠️ NEVER cache - risk of double booking'
        },
        {
            name: 'Calculated Price',
            category: 'realtime',
            storePinia: false,
            serverFetch: 'always',
            cache: 'never',
            status: 'n/a',
            value: null,
            notes: '⚠️ Server computes final price'
        },
        {
            name: 'Inventory/Stock',
            category: 'realtime',
            storePinia: false,
            serverFetch: 'always',
            cache: 'never',
            status: 'n/a',
            value: null,
            notes: 'Real-time stock count'
        },
        
        // === SENSITIVE DATA ===
        {
            name: 'Payment Amount',
            category: 'sensitive',
            storePinia: false,
            serverFetch: 'always',
            cache: 'never',
            status: 'n/a',
            value: null,
            notes: '🔒 Server-side only - never trust frontend'
        },
        {
            name: 'Promo Validation',
            category: 'sensitive',
            storePinia: false,
            serverFetch: 'always',
            cache: 'never',
            status: 'n/a',
            value: null,
            notes: '🔒 Server validates to prevent abuse'
        },
    ];
});

// Category colors
const getCategoryColor = (category: string) => {
    switch(category) {
        case 'static': return 'text-green-400 bg-green-900/30';
        case 'semi-dynamic': return 'text-yellow-400 bg-yellow-900/30';
        case 'realtime': return 'text-red-400 bg-red-900/30';
        case 'sensitive': return 'text-purple-400 bg-purple-900/30';
        default: return 'text-gray-400 bg-gray-900/30';
    }
};

const getStatusIcon = (status: string) => {
    switch(status) {
        case 'loaded': return { icon: '✓', class: 'text-green-400' };
        case 'not-loaded': return { icon: '○', class: 'text-yellow-400' };
        case 'n/a': return { icon: '—', class: 'text-gray-500' };
        default: return { icon: '?', class: 'text-gray-400' };
    }
};

// Computed data for each tab
const inertiaData = computed(() => ({
    url: page.url,
    component: page.component,
    version: page.version,
    props: page.props,
    propsKeys: Object.keys(page.props),
    tenant: (page.props as any).tenant || null,
    sharedResources: (page.props as any).sharedResources || null,
    resources: (page.props as any).resources || null,
    resourceType: (page.props as any).resourceType || null,
}));

const piniaData = computed(() => ({
    tenantStore: {
        tenant: tenantStore.tenant,
        isHydrated: tenantStore.isHydrated,
        isTenantContext: tenantStore.isTenantContext,
        tenantName: tenantStore.tenantName,
        resourceRoutes: tenantStore.resourceRoutes,
    },
    resourceStore: {
        units: resourceStore.units,
        activities: resourceStore.activities,
        isHydrated: resourceStore.isHydrated,
        allUnits: resourceStore.allUnits,
        allActivities: resourceStore.allActivities,
        totalResources: resourceStore.totalResources,
    },
}));

const injectData = computed(() => ({
    tenant: injectedTenant,
}));

// Summary stats
const matrixSummary = computed(() => {
    const items = dataStorageMatrix.value;
    return {
        total: items.length,
        loaded: items.filter(i => i.status === 'loaded').length,
        notLoaded: items.filter(i => i.status === 'not-loaded').length,
        static: items.filter(i => i.category === 'static').length,
        realtime: items.filter(i => i.category === 'realtime').length,
    };
});

// Performance tracking
const performanceData = ref({
    mountTime: 0,
    hydrationCalls: 0,
    lastUpdate: '',
});

// Track store hydration
watch(() => tenantStore.isHydrated, (val) => {
    if (val) performanceData.value.hydrationCalls++;
});

watch(() => resourceStore.isHydrated, (val) => {
    if (val) performanceData.value.hydrationCalls++;
});

// Lifecycle
let mountStart: number;
onMounted(() => {
    mountStart = performance.now();
    performanceData.value.mountTime = performance.now() - mountStart;
    performanceData.value.lastUpdate = new Date().toISOString();
});

// Position classes
const positionClasses = computed(() => {
    const positions: Record<string, string> = {
        'bottom-right': 'bottom-4 right-4',
        'bottom-left': 'bottom-4 left-4',
        'top-right': 'top-4 right-4',
        'top-left': 'top-4 left-4',
    };
    return positions[props.position];
});

// Methods
function copyToClipboard(data: any) {
    navigator.clipboard.writeText(JSON.stringify(data, null, 2));
}

function refreshData() {
    performanceData.value.lastUpdate = new Date().toISOString();
}

// Minimize storage
const storageKey = 'tenant-debugger-minimized';
onMounted(() => {
    const stored = localStorage.getItem(storageKey);
    if (stored === 'true') isExpanded.value = false;
});

watch(isExpanded, (val) => {
    localStorage.setItem(storageKey, (!val).toString());
});

// Selected matrix item for detail view
const selectedMatrixItem = ref<DataMatrixItem | null>(null);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="enabled"
            :class="[
                'fixed z-[9999] font-mono text-xs',
                positionClasses
            ]"
        >
            <!-- Collapsed Button -->
            <button
                v-if="!isExpanded"
                @click="isExpanded = true"
                class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-all"
            >
                <span class="text-green-400">🔍</span>
                <span>Debugger</span>
                <span class="text-xs text-gray-400">({{ matrixSummary.loaded }}/{{ matrixSummary.total }})</span>
            </button>

            <!-- Expanded Panel -->
            <div
                v-else
                class="bg-gray-900/95 backdrop-blur-sm text-white rounded-lg shadow-2xl border border-gray-700 w-[600px] max-h-[700px] flex flex-col overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-3 py-2 border-b border-gray-700 bg-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="text-green-400">🔍</span>
                        <span class="font-semibold">TenantLayout Debugger</span>
                        <span class="text-xs text-gray-400">
                            {{ matrixSummary.loaded }} loaded
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="refreshData" 
                            class="text-gray-400 hover:text-white p-1"
                            title="Refresh"
                        >
                            ↻
                        </button>
                        <button 
                            @click="isExpanded = false" 
                            class="text-gray-400 hover:text-white p-1"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-700 bg-gray-800/50 overflow-x-auto">
                    <button
                        v-for="tab in ['matrix', 'inertia', 'pinia', 'inject', 'performance'] as const"
                        :key="tab"
                        @click="activeTab = tab"
                        :class="[
                            'px-3 py-2 capitalize transition-colors whitespace-nowrap',
                            activeTab === tab 
                                ? 'text-blue-400 border-b-2 border-blue-400' 
                                : 'text-gray-400 hover:text-white'
                        ]"
                    >
                        <span v-if="tab === 'matrix'">📊 Data Matrix</span>
                        <span v-else>{{ tab }}</span>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-auto p-3">
                    <!-- Data Storage Matrix Tab -->
                    <div v-if="activeTab === 'matrix'" class="space-y-3">
                        <!-- Legend -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="text-gray-400 mb-2 text-xs">Category Legend</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2 py-0.5 rounded text-green-400 bg-green-900/30">📗 Static</span>
                                <span class="px-2 py-0.5 rounded text-yellow-400 bg-yellow-900/30">📙 Semi-Dynamic</span>
                                <span class="px-2 py-0.5 rounded text-red-400 bg-red-900/30">📕 Real-time</span>
                                <span class="px-2 py-0.5 rounded text-purple-400 bg-purple-900/30">🔒 Sensitive</span>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-4 gap-2">
                            <div class="bg-gray-800 rounded p-2 text-center">
                                <div class="text-xl text-green-400">{{ matrixSummary.loaded }}</div>
                                <div class="text-gray-400 text-xs">Loaded</div>
                            </div>
                            <div class="bg-gray-800 rounded p-2 text-center">
                                <div class="text-xl text-yellow-400">{{ matrixSummary.notLoaded }}</div>
                                <div class="text-gray-400 text-xs">Pending</div>
                            </div>
                            <div class="bg-gray-800 rounded p-2 text-center">
                                <div class="text-xl text-blue-400">{{ piniaData.tenantStore.isHydrated ? '✓' : '○' }}</div>
                                <div class="text-gray-400 text-xs">Tenant Store</div>
                            </div>
                            <div class="bg-gray-800 rounded p-2 text-center">
                                <div class="text-xl text-blue-400">{{ piniaData.resourceStore.isHydrated ? '✓' : '○' }}</div>
                                <div class="text-gray-400 text-xs">Resource Store</div>
                            </div>
                        </div>

                        <!-- Matrix Table -->
                        <div class="bg-gray-800 rounded overflow-hidden">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-left text-gray-400 border-b border-gray-700 bg-gray-900">
                                        <th class="p-2">Data</th>
                                        <th class="p-2">Category</th>
                                        <th class="p-2">Pinia?</th>
                                        <th class="p-2">Cache</th>
                                        <th class="p-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="item in dataStorageMatrix" 
                                        :key="item.name"
                                        class="border-b border-gray-700/50 hover:bg-gray-700/30 cursor-pointer transition-colors"
                                        @click="selectedMatrixItem = selectedMatrixItem?.name === item.name ? null : item"
                                    >
                                        <td class="p-2">
                                            <div class="font-medium text-white">{{ item.name }}</div>
                                            <div class="text-gray-500 text-xs">{{ item.notes }}</div>
                                        </td>
                                        <td class="p-2">
                                            <span :class="['px-1.5 py-0.5 rounded text-xs', getCategoryColor(item.category)]">
                                                {{ item.category }}
                                            </span>
                                        </td>
                                        <td class="p-2">
                                            <span :class="item.storePinia ? 'text-green-400' : 'text-red-400'">
                                                {{ item.storePinia ? '✓' : '✗' }}
                                            </span>
                                        </td>
                                        <td class="p-2">
                                            <span :class="item.cache === 'never' ? 'text-red-400' : 'text-gray-300'">
                                                {{ item.cacheTime || item.cache }}
                                            </span>
                                        </td>
                                        <td class="p-2">
                                            <span :class="getStatusIcon(item.status).class">
                                                {{ getStatusIcon(item.status).icon }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Selected Item Detail -->
                        <div v-if="selectedMatrixItem" class="bg-gray-800 rounded p-3 border border-gray-600">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-white">{{ selectedMatrixItem.name }}</span>
                                <button 
                                    @click="copyToClipboard(selectedMatrixItem.value)"
                                    class="text-xs text-blue-400 hover:text-blue-300"
                                    v-if="selectedMatrixItem.value"
                                >
                                    Copy Value
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                                <div>
                                    <span class="text-gray-400">Server Fetch:</span>
                                    <span class="ml-1 text-white">{{ selectedMatrixItem.serverFetch }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Cache:</span>
                                    <span class="ml-1 text-white">{{ selectedMatrixItem.cacheTime || selectedMatrixItem.cache }}</span>
                                </div>
                            </div>
                            <div v-if="selectedMatrixItem.value !== null" class="mt-2">
                                <div class="text-gray-400 text-xs mb-1">Current Value:</div>
                                <pre class="text-xs overflow-auto max-h-32 text-gray-300 bg-gray-900 p-2 rounded">{{ JSON.stringify(selectedMatrixItem.value, null, 2) }}</pre>
                            </div>
                            <div v-else class="text-gray-500 italic text-xs mt-2">
                                No data loaded for this item
                            </div>
                        </div>

                        <!-- Best Practices -->
                        <details class="bg-blue-900/20 border border-blue-700/50 rounded">
                            <summary class="p-2 cursor-pointer text-blue-400 hover:text-blue-300">
                                💡 Best Practices Guide
                            </summary>
                            <div class="p-3 border-t border-blue-700/50 text-xs space-y-2">
                                <div class="text-green-400">📗 Static → Cache in Pinia, refresh on session</div>
                                <div class="text-yellow-400">📙 Semi-Dynamic → Cache with short TTL (5-10 min)</div>
                                <div class="text-red-400">📕 Real-time → NEVER cache, always server fetch</div>
                                <div class="text-purple-400">🔒 Sensitive → Server-side only, never expose to frontend</div>
                            </div>
                        </details>
                    </div>

                    <!-- Inertia Tab -->
                    <div v-if="activeTab === 'inertia'" class="space-y-3">
                        <!-- URL & Component -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="text-gray-400 mb-1">URL</div>
                            <code class="text-green-400">{{ inertiaData.url }}</code>
                        </div>
                        <div class="bg-gray-800 rounded p-2">
                            <div class="text-gray-400 mb-1">Component</div>
                            <code class="text-blue-400">{{ inertiaData.component }}</code>
                        </div>

                        <!-- Props Keys -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-400">Props Keys ({{ inertiaData.propsKeys.length }})</span>
                                <button 
                                    @click="copyToClipboard(inertiaData.props)"
                                    class="text-xs text-blue-400 hover:text-blue-300"
                                >
                                    Copy All
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="key in inertiaData.propsKeys" 
                                    :key="key"
                                    class="bg-gray-700 px-2 py-0.5 rounded text-yellow-300"
                                >
                                    {{ key }}
                                </span>
                            </div>
                        </div>

                        <!-- Tenant Data -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-400">page.props.tenant</span>
                                <span 
                                    :class="inertiaData.tenant ? 'text-green-400' : 'text-red-400'"
                                >
                                    {{ inertiaData.tenant ? '✓ Found' : '✗ Not Found' }}
                                </span>
                            </div>
                            <pre v-if="inertiaData.tenant" class="text-xs overflow-auto max-h-32 text-gray-300">{{ JSON.stringify(inertiaData.tenant, null, 2) }}</pre>
                        </div>

                        <!-- Shared Resources -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-400">page.props.sharedResources</span>
                                <span 
                                    :class="inertiaData.sharedResources ? 'text-green-400' : 'text-red-400'"
                                >
                                    {{ inertiaData.sharedResources ? '✓ Found' : '✗ Not Found' }}
                                </span>
                            </div>
                            <div v-if="inertiaData.sharedResources" class="text-xs text-gray-300 mb-1">
                                Units: {{ inertiaData.sharedResources.units?.length || 0 }} | 
                                Activities: {{ inertiaData.sharedResources.activities?.length || 0 }}
                            </div>
                            <details v-if="inertiaData.sharedResources">
                                <summary class="cursor-pointer text-gray-400 hover:text-white text-xs">
                                    View data
                                </summary>
                                <pre class="text-xs overflow-auto max-h-32 text-gray-300 mt-1">{{ JSON.stringify(inertiaData.sharedResources, null, 2) }}</pre>
                            </details>
                        </div>

                        <!-- Page-specific Resource -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-400">page.props.resource (detail)</span>
                                <span :class="inertiaData.resources ? 'text-green-400' : 'text-gray-500'">
                                    {{ inertiaData.resources ? `✓ ${inertiaData.resourceType}` : '— Not on detail page' }}
                                </span>
                            </div>
                        </div>

                        <!-- Full Props (collapsible) -->
                        <details class="bg-gray-800 rounded">
                            <summary class="p-2 cursor-pointer text-gray-400 hover:text-white">
                                Full page.props (raw)
                            </summary>
                            <pre class="p-2 text-xs overflow-auto max-h-48 text-gray-300 border-t border-gray-700">{{ JSON.stringify(inertiaData.props, null, 2) }}</pre>
                        </details>
                    </div>

                    <!-- Pinia Tab -->
                    <div v-if="activeTab === 'pinia'" class="space-y-3">
                        <!-- Hydration Status -->
                        <div class="bg-green-900/20 border border-green-700/50 rounded p-2" v-if="piniaData.tenantStore.isHydrated && piniaData.resourceStore.isHydrated">
                            <span class="text-green-400">✓ All stores hydrated from Inertia props</span>
                        </div>
                        <div class="bg-yellow-900/20 border border-yellow-700/50 rounded p-2" v-else>
                            <span class="text-yellow-400">○ Stores pending hydration</span>
                        </div>

                        <!-- Tenant Store -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-purple-400 font-semibold">useTenantStore</span>
                                <span 
                                    :class="piniaData.tenantStore.isHydrated ? 'text-green-400' : 'text-yellow-400'"
                                >
                                    {{ piniaData.tenantStore.isHydrated ? '✓ Hydrated' : '○ Not Hydrated' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-gray-400">isTenantContext:</span>
                                    <span :class="piniaData.tenantStore.isTenantContext ? 'text-green-400' : 'text-red-400'">
                                        {{ piniaData.tenantStore.isTenantContext }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400">tenantName:</span>
                                    <span class="text-white">{{ piniaData.tenantStore.tenantName || '(empty)' }}</span>
                                </div>
                            </div>
                            <details class="mt-2">
                                <summary class="cursor-pointer text-gray-400 hover:text-white text-xs">
                                    View tenant data
                                </summary>
                                <pre class="text-xs overflow-auto max-h-32 text-gray-300 mt-1">{{ JSON.stringify(piniaData.tenantStore.tenant, null, 2) }}</pre>
                            </details>
                            <details class="mt-1">
                                <summary class="cursor-pointer text-gray-400 hover:text-white text-xs">
                                    View resourceRoutes
                                </summary>
                                <pre class="text-xs overflow-auto max-h-32 text-gray-300 mt-1">{{ JSON.stringify(piniaData.tenantStore.resourceRoutes, null, 2) }}</pre>
                            </details>
                        </div>

                        <!-- Resource Store -->
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-purple-400 font-semibold">useResourceStore</span>
                                <span 
                                    :class="piniaData.resourceStore.isHydrated ? 'text-green-400' : 'text-yellow-400'"
                                >
                                    {{ piniaData.resourceStore.isHydrated ? '✓ Hydrated' : '○ Not Hydrated' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs mb-2">
                                <div class="bg-gray-700 rounded p-2 text-center">
                                    <div class="text-2xl text-blue-400">{{ piniaData.resourceStore.units.length }}</div>
                                    <div class="text-gray-400">Units</div>
                                </div>
                                <div class="bg-gray-700 rounded p-2 text-center">
                                    <div class="text-2xl text-green-400">{{ piniaData.resourceStore.activities.length }}</div>
                                    <div class="text-gray-400">Activities</div>
                                </div>
                                <div class="bg-gray-700 rounded p-2 text-center">
                                    <div class="text-2xl text-yellow-400">{{ piniaData.resourceStore.totalResources }}</div>
                                    <div class="text-gray-400">Total</div>
                                </div>
                            </div>
                            <details>
                                <summary class="cursor-pointer text-gray-400 hover:text-white text-xs">
                                    View units ({{ piniaData.resourceStore.units.length }})
                                </summary>
                                <pre class="text-xs overflow-auto max-h-32 text-gray-300 mt-1">{{ JSON.stringify(piniaData.resourceStore.units, null, 2) }}</pre>
                            </details>
                            <details class="mt-1">
                                <summary class="cursor-pointer text-gray-400 hover:text-white text-xs">
                                    View activities ({{ piniaData.resourceStore.activities.length }})
                                </summary>
                                <pre class="text-xs overflow-auto max-h-32 text-gray-300 mt-1">{{ JSON.stringify(piniaData.resourceStore.activities, null, 2) }}</pre>
                            </details>
                        </div>
                    </div>

                    <!-- Inject Tab -->
                    <div v-if="activeTab === 'inject'" class="space-y-3">
                        <div class="bg-gray-800 rounded p-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-cyan-400 font-semibold">inject('tenant')</span>
                                <span 
                                    :class="injectData.tenant ? 'text-green-400' : 'text-red-400'"
                                >
                                    {{ injectData.tenant ? '✓ Provided' : '✗ Not Provided' }}
                                </span>
                            </div>
                            <p class="text-gray-400 text-xs mb-2">
                                This is the tenant object provided by TenantLayout via Vue's provide/inject system.
                            </p>
                            <pre v-if="injectData.tenant" class="text-xs overflow-auto max-h-48 text-gray-300">{{ JSON.stringify(injectData.tenant, null, 2) }}</pre>
                        </div>

                        <div class="bg-blue-900/30 border border-blue-700 rounded p-3">
                            <div class="text-blue-400 font-semibold mb-1">💡 Usage Options</div>
                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-gray-300">1. Via inject (simple):</span>
                                    <code class="block mt-1 bg-gray-800 p-1 rounded text-green-400">const tenant = inject('tenant')</code>
                                </div>
                                <div>
                                    <span class="text-gray-300">2. Via Pinia (recommended):</span>
                                    <code class="block mt-1 bg-gray-800 p-1 rounded text-green-400">const { tenantName } = storeToRefs(useTenantStore())</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Tab -->
                    <div v-if="activeTab === 'performance'" class="space-y-3">
                        <div class="bg-gray-800 rounded p-2">
                            <div class="text-gray-400 mb-2">Mount Performance</div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-gray-700 rounded p-2">
                                    <div class="text-xs text-gray-400">Mount Time</div>
                                    <div class="text-lg text-green-400">{{ performanceData.mountTime.toFixed(2) }}ms</div>
                                </div>
                                <div class="bg-gray-700 rounded p-2">
                                    <div class="text-xs text-gray-400">Hydration Calls</div>
                                    <div class="text-lg text-blue-400">{{ performanceData.hydrationCalls }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-800 rounded p-2">
                            <div class="text-gray-400 mb-1">Last Update</div>
                            <code class="text-yellow-400">{{ performanceData.lastUpdate }}</code>
                        </div>

                        <div class="bg-gray-800 rounded p-2">
                            <div class="text-gray-400 mb-2">Data Source Summary</div>
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-left text-gray-400 border-b border-gray-700">
                                        <th class="pb-1">Source</th>
                                        <th class="pb-1">Status</th>
                                        <th class="pb-1">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-1">Inertia props.tenant</td>
                                        <td :class="inertiaData.tenant ? 'text-green-400' : 'text-red-400'">
                                            {{ inertiaData.tenant ? '✓' : '✗' }}
                                        </td>
                                        <td class="text-gray-300">{{ inertiaData.tenant?.name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Inertia sharedResources</td>
                                        <td :class="inertiaData.sharedResources ? 'text-green-400' : 'text-red-400'">
                                            {{ inertiaData.sharedResources ? '✓' : '✗' }}
                                        </td>
                                        <td class="text-gray-300">
                                            {{ inertiaData.sharedResources 
                                                ? `${inertiaData.sharedResources.units?.length || 0}U / ${inertiaData.sharedResources.activities?.length || 0}A`
                                                : 'N/A' 
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">TenantStore</td>
                                        <td :class="piniaData.tenantStore.isHydrated ? 'text-green-400' : 'text-yellow-400'">
                                            {{ piniaData.tenantStore.isHydrated ? '✓' : '○' }}
                                        </td>
                                        <td class="text-gray-300">{{ piniaData.tenantStore.tenantName || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">ResourceStore</td>
                                        <td :class="piniaData.resourceStore.isHydrated ? 'text-green-400' : 'text-yellow-400'">
                                            {{ piniaData.resourceStore.isHydrated ? '✓' : '○' }}
                                        </td>
                                        <td class="text-gray-300">{{ piniaData.resourceStore.totalResources }} items</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">inject('tenant')</td>
                                        <td :class="injectData.tenant ? 'text-green-400' : 'text-red-400'">
                                            {{ injectData.tenant ? '✓' : '✗' }}
                                        </td>
                                        <td class="text-gray-300">{{ injectData.tenant?.name ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-3 py-2 border-t border-gray-700 bg-gray-800/50 text-gray-500 text-xs flex justify-between">
                    <span>Development Only</span>
                    <span>{{ inertiaData.component }}</span>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* Scrollbar styling for the debugger */
:deep(::-webkit-scrollbar) {
    width: 6px;
    height: 6px;
}

:deep(::-webkit-scrollbar-track) {
    background: #1f2937;
}

:deep(::-webkit-scrollbar-thumb) {
    background: #4b5563;
    border-radius: 3px;
}

:deep(::-webkit-scrollbar-thumb:hover) {
    background: #6b7280;
}
</style>
