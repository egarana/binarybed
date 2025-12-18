<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Models\Rate;
use App\Models\Unit;
use App\Repositories\RateRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateRate
{
    use HandlesTenancy;

    public function __construct(
        protected RateRepository $rateRepository
    ) {}

    /**
     * Update a rate in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $resourceSlug
     * @param string $rateSlug
     * @param array $data
     * @return Rate
     */
    public function execute(string $tenantId, string $resourceSlug, string $rateSlug, array $data): Rate
    {
        return $this->executeInTenantContext($tenantId, function () use ($resourceSlug, $rateSlug, $data) {
            // Find resource (Unit or Activity) by slug
            $resource = Unit::where('slug', $resourceSlug)->first()
                ?? Activity::where('slug', $resourceSlug)->first();

            if (!$resource) {
                throw new ModelNotFoundException("Resource with slug '{$resourceSlug}' not found");
            }

            // Find rate through the resource's rates relationship
            $rate = $resource->rates()->where('slug', $rateSlug)->firstOrFail();

            // Prevent modifying name and slug for default rates
            if ($rate->is_default) {
                unset($data['name'], $data['slug']);
            }

            return $this->rateRepository->update($rate, $data);
        });
    }
}
