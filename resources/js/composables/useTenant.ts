import { inject } from 'vue';
import type { Tenant } from '@/types/tenant';

/**
 * Composable to access tenant data from TenantLayout via inject
 * 
 * This composable automatically retrieves tenant data that was provided
 * by TenantLayout, so you don't need to manually access Inertia props.
 * 
 * @returns {Tenant} The current tenant data (id, name, domain)
 * @throws {Error} If used outside of TenantLayout context
 * 
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useTenant } from '@/composables/useTenant';
 * 
 * const tenant = useTenant();
 * </script>
 * 
 * <template>
 *   <div>
 *     <h1>{{ tenant.name }}</h1>
 *     <p>{{ tenant.domain }}</p>
 *   </div>
 * </template>
 * ```
 */
export function useTenant(): Tenant {
    const tenant = inject<Tenant>('tenant');

    if (!tenant) {
        throw new Error('useTenant() must be used within TenantLayout component');
    }

    return tenant;
}

