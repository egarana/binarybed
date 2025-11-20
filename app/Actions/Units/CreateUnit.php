<?php

namespace App\Actions\Units;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\HandlesTenancy;

class CreateUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository
    ) {}

    /**
     * Create a new unit in the specified tenant's database.
     *
     * @param array $data
     * @return Unit
     */
    public function execute(array $data): Unit
    {
        $tenantId = $data['tenant_id'];

        return $this->executeInTenantContext($tenantId, function ($tenant) use ($data) {
            $unit = $this->unitRepository->create($data);

            return $unit;
        });
    }
}