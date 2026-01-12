<script setup lang="ts">
import { computed } from 'vue';
import { Bath, Bed, Binoculars, Users, Clock, Footprints, ShieldCheck, Ticket } from 'lucide-vue-next';
import { type Resource } from '@/stores/useResourceStore';

interface Props {
    resource: Resource;
    resourceType: 'units' | 'activities';
    tenantName?: string;
}

const props = defineProps<Props>();

// Computed for Activity Highlights (Placeholder/Hardcoded for now as per plan)
// In future, this will also pull from resource.features or specific columns
const isActivity = computed(() => props.resourceType === 'activities');
</script>

<template>
    <div class="px-6 py-6 space-y-4 md:px-0">
        <!-- UNIT VERSION -->
        <template v-if="!isActivity">
            <p class="text-muted-foreground font-medium uppercase text-sm tracking-wider">
                {{ resource.subtitle || tenantName || 'Entire cabin' }}
            </p>
            <h1 class="text-2xl font-semibold">
                {{ resource.name }}
            </h1>
            
            <ul class="flex flex-wrap text-sm gap-x-2 gap-y-1">
                <li class="flex items-center gap-2">
                    <Bed class="w-4 h-4 text-muted-foreground" />
                    <span>{{ resource.bedroom_count || 1 }} Bedroom{{ (resource.bedroom_count || 1) > 1 ? 's' : '' }}</span>
                </li>
                <li>·</li>
                <li class="flex items-center gap-2">
                    <Users class="w-4 h-4 text-muted-foreground" />
                    <span>{{ resource.max_guests || 2 }} Guest{{ (resource.max_guests || 2) > 1 ? 's' : '' }}</span>
                </li>
                <li>·</li>
                <li class="flex items-center gap-2">
                    <Bath class="w-4 h-4 text-muted-foreground" />
                    <span>{{ resource.bathroom_count || 1 }} Bathroom{{ (resource.bathroom_count || 1) > 1 ? 's' : '' }}</span>
                </li>
                <template v-if="resource.view">
                    <li>·</li>
                    <li class="flex items-center gap-2">
                        <Binoculars class="w-4 h-4 text-muted-foreground" />
                        <span>{{ resource.view }}</span>
                    </li>
                </template>
            </ul>
        </template>

        <!-- ACTIVITY VERSION (Hardcoded/Static for now) -->
        <template v-else>
            <p class="text-muted-foreground font-medium uppercase text-sm tracking-wider">
                {{ resource.subtitle || tenantName || 'Guided Adventure' }}
            </p>
            <h1 class="text-2xl font-semibold">
                {{ resource.name }}
            </h1>
            
            <ul class="flex flex-wrap text-sm gap-x-2 gap-y-1">
                <li class="flex items-center gap-2">
                    <Clock class="w-4 h-4 text-muted-foreground" />
                    <span>6-7 Hours</span>
                </li>
                <li>·</li>
                <li class="flex items-center gap-2">
                    <Footprints class="w-4 h-4 text-muted-foreground" />
                    <span>Moderate</span>
                </li>
                <li>·</li>
                <li class="flex items-center gap-2">
                    <ShieldCheck class="w-4 h-4 text-muted-foreground" />
                    <span>Insurance Included</span>
                </li>
                <li>·</li>
                <li class="flex items-center gap-2">
                    <Ticket class="w-4 h-4 text-muted-foreground" />
                    <span>Mobile Ticket</span>
                </li>
            </ul>
        </template>
    </div>
</template>
