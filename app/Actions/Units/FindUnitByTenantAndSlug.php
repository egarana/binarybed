<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;

class FindUnitByTenantAndSlug
{
    use HandlesTenancy;

    /**
     * Find a unit by tenant ID and slug.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function execute(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            return [
                'unit' => $unit,
                'tenant' => $tenant
            ];
        });
    }
}
