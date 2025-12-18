<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Activity;
use App\Models\Rate;
use App\Models\Unit;
use App\Repositories\RateRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class DeleteRate
{
    use HandlesTenancy;

    public function __construct(
        protected RateRepository $rateRepository
    ) {}

    /**
     * Delete a rate in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $resourceSlug
     * @param string $rateSlug
     */
    public function execute(string $tenantId, string $resourceSlug, string $rateSlug): void
    {
        $this->executeInTenantContext($tenantId, function () use ($resourceSlug, $rateSlug) {
            // Find resource (Unit or Activity) by slug
            $resource = Unit::where('slug', $resourceSlug)->first()
                ?? Activity::where('slug', $resourceSlug)->first();

            if (!$resource) {
                throw new ModelNotFoundException("Resource with slug '{$resourceSlug}' not found");
            }

            // Find rate through the resource's rates relationship
            $rate = $resource->rates()->where('slug', $rateSlug)->firstOrFail();

            if ($rate->is_default) {
                throw ValidationException::withMessages([
                    'rate' => 'Cannot delete the default rate. You can edit it but not delete.',
                ]);
            }

            $this->rateRepository->delete($rate);
        });
    }
}
