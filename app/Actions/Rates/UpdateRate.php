<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;
use App\Repositories\RateRepository;

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
     * @param string $slug
     * @param array $data
     * @return Rate
     */
    public function execute(string $tenantId, string $slug, array $data): Rate
    {
        return $this->executeInTenantContext($tenantId, function () use ($slug, $data) {
            $rate = Rate::where('slug', $slug)->firstOrFail();

            return $this->rateRepository->update($rate, $data);
        });
    }
}
