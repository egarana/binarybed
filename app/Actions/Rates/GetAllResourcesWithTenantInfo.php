<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Unit;

class GetAllResourcesWithTenantInfo
{
    use HandlesTenancy;

    /**
     * Get all resources (Units + Activities) from all tenants with tenant info.
     * Optimized for performance with per-tenant limit and total limit.
     *
     * @param string|null $search Search query (searches resource name and tenant name)
     * @param int $limit Max results per tenant
     * @return array
     */
    public function execute(?string $search = null, int $limit = 50): array
    {
        $resources = [];

        // Get all tenants
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Check if tenant name matches search (include all resources from matching tenant)
            $tenantMatches = $search && stripos($tenant->name, $search) !== false;

            // Execute in tenant context using the trait helper
            $tenantResources = $this->executeInTenantContext($tenant->id, function () use ($tenant, $search, $tenantMatches, $limit) {
                $items = [];

                // Get Units
                $unitsQuery = Unit::query();
                if (!$tenantMatches && $search) {
                    $unitsQuery->where('name', 'like', "%{$search}%");
                }
                $units = $unitsQuery->limit($limit)->get();

                foreach ($units as $unit) {
                    $items[] = [
                        'value' => "Unit:{$tenant->id}:{$unit->id}",
                        'label' => "{$unit->name} - {$tenant->name} (Unit)",
                        'type' => 'Unit',
                        'id' => $unit->id,
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'resource_name' => $unit->name,
                    ];
                }

                // Get Activities
                $activitiesQuery = Activity::query();
                if (!$tenantMatches && $search) {
                    $activitiesQuery->where('name', 'like', "%{$search}%");
                }
                $activities = $activitiesQuery->limit($limit)->get();

                foreach ($activities as $activity) {
                    $items[] = [
                        'value' => "Activity:{$tenant->id}:{$activity->id}",
                        'label' => "{$activity->name} - {$tenant->name} (Activity)",
                        'type' => 'Activity',
                        'id' => $activity->id,
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'resource_name' => $activity->name,
                    ];
                }

                return $items;
            });

            $resources = array_merge($resources, $tenantResources);
        }

        // Sort by label for consistent ordering
        usort($resources, fn($a, $b) => strcmp($a['label'], $b['label']));

        // Limit total results
        return array_slice($resources, 0, 100);
    }
}
