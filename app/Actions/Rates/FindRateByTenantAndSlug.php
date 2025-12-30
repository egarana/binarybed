<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Models\Unit;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindRateByTenantAndSlug
{
    use HandlesTenancy;

    /**
     * Find a rate by tenant ID, resource slug, and rate slug.
     *
     * @param string $tenantId
     * @param string $resourceSlug
     * @param string $rateSlug
     * @return array
     */
    public function execute(string $tenantId, string $resourceSlug, string $rateSlug): array
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($resourceSlug, $rateSlug) {
            // Find resource (Unit or Activity) by slug
            $resource = Unit::where('slug', $resourceSlug)->first()
                ?? Activity::where('slug', $resourceSlug)->first();

            if (!$resource) {
                throw new ModelNotFoundException("Resource with slug '{$resourceSlug}' not found");
            }

            // Find rate through the resource's rates relationship
            $rate = $resource->rates()->where('slug', $rateSlug)->firstOrFail();

            // Get resource information
            $resourceName = $resource->name;
            $rateableType = class_basename(get_class($resource));

            // Format product display like in GetAllResourcesWithTenantInfo
            // Format: "{resource_name} - {tenant_name} (Type)"
            $productDisplay = "{$resourceName} - {$tenant->name} ({$rateableType})";

            return [
                'id' => $rate->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'resource_name' => $resourceName,
                'resource_slug' => $resource->slug,
                'rateable_type' => $rateableType,
                'product_display' => $productDisplay,
                'name' => $rate->name,
                'slug' => $rate->slug,
                'description' => $rate->description,
                'price' => $rate->price,
                'currency' => $rate->currency,
                'price_type' => $rate->price_type,
                'is_active' => $rate->is_active,
                'is_default' => $rate->is_default,
            ];
        });
    }
}
