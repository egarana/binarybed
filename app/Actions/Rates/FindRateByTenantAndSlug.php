<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;

class FindRateByTenantAndSlug
{
    use HandlesTenancy;

    /**
     * Find a rate by tenant ID and slug.
     *
     * @param string $tenantId
     * @param string $slug
     * @return array
     */
    public function execute(string $tenantId, string $slug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug) {
            $rate = Rate::where('slug', $slug)->firstOrFail();

            return [
                'id' => $rate->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'name' => $rate->name,
                'slug' => $rate->slug,
                'description' => $rate->description,
                'price' => $rate->price,
                'currency' => $rate->currency,
                'is_active' => $rate->is_active,
            ];
        });
    }
}
