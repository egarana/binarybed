<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Models\Unit;

class GetResourcesForTenant
{
    use HandlesTenancy;

    /**
     * Get all resources (Units + Activities) for a tenant.
     * Returns formatted options for Select component.
     *
     * @param string $tenantId
     * @param string|null $search Search query
     * @return array
     */
    public function execute(string $tenantId, ?string $search = null): array
    {
        return $this->executeInTenantContext($tenantId, function () use ($search) {
            $resources = [];

            // Get Units
            $unitsQuery = Unit::query();
            if ($search) {
                $unitsQuery->where('name', 'like', "%{$search}%");
            }
            $units = $unitsQuery->limit(50)->get();

            foreach ($units as $unit) {
                $resources[] = [
                    'value' => 'Unit:' . $unit->id,
                    'label' => $unit->name,
                    'type' => 'Unit',
                    'id' => $unit->id,
                ];
            }

            // Get Activities
            $activitiesQuery = Activity::query();
            if ($search) {
                $activitiesQuery->where('name', 'like', "%{$search}%");
            }
            $activities = $activitiesQuery->limit(50)->get();

            foreach ($activities as $activity) {
                $resources[] = [
                    'value' => 'Activity:' . $activity->id,
                    'label' => $activity->name,
                    'type' => 'Activity',
                    'id' => $activity->id,
                ];
            }

            return $resources;
        });
    }
}
