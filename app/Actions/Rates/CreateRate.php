<?php

namespace App\Actions\Rates;

use App\Models\Rate;
use App\Repositories\RateRepository;
use App\HandlesTenancy;

class CreateRate
{
    use HandlesTenancy;

    public function __construct(
        protected RateRepository $rateRepository
    ) {}

    /**
     * Create a new rate in the specified tenant's database.
     * Returns an array with the rate and resource slug for redirect purposes.
     *
     * @param array $data
     * @return array{rate: Rate, resource_slug: string|null}
     */
    public function execute(array $data): array
    {
        $tenantId = $data['tenant_id'];

        return $this->executeInTenantContext($tenantId, function () use ($data) {
            $rate = $this->rateRepository->create($data);

            // Get resource slug while still in tenant context
            $resourceSlug = null;
            if ($rate->rateable) {
                $resourceSlug = $rate->rateable->slug;
            }

            return [
                'rate' => $rate,
                'resource_slug' => $resourceSlug,
            ];
        });
    }
}
