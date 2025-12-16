<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;
use App\Repositories\RateRepository;

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
     * @param string $slug
     * @return void
     */
    public function execute(string $tenantId, string $slug): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug) {
            $rate = Rate::where('slug', $slug)->firstOrFail();

            $this->rateRepository->delete($rate);
        });
    }
}
