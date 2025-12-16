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
            $rate = Rate::with('rateable')->where('slug', $slug)->firstOrFail();

            // Get resource information from the rateable relationship
            $rateable = $rate->rateable;
            $resourceName = $rateable ? $rateable->name : '';
            $rateableType = $rateable ? class_basename($rate->rateable_type) : '';

            // Format product display like in GetAllResourcesWithTenantInfo
            // Format: "{resource_name} - {tenant_name} (Type)"
            $productDisplay = $rateable
                ? "{$resourceName} - {$tenant->name} ({$rateableType})"
                : $tenant->name;

            return [
                'id' => $rate->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'resource_name' => $resourceName,
                'rateable_type' => $rateableType,
                'product_display' => $productDisplay,
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
