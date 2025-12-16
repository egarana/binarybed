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
     *
     * @param array $data
     * @return Rate
     */
    public function execute(array $data): Rate
    {
        $tenantId = $data['tenant_id'];

        return $this->executeInTenantContext($tenantId, function () use ($data) {
            return $this->rateRepository->create($data);
        });
    }
}
