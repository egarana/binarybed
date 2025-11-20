<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Repositories\UnitRepository;

class UpdateUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository
    ) {}

    /**
     * Update a unit in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $slug
     * @param array $data
     * @return Unit
     */
    public function execute(string $tenantId, string $slug, array $data): Unit
    {
        return $this->executeInTenantContext($tenantId, function ($tenant) use ($slug, $data) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            return $this->unitRepository->update($unit, $data);
        });
    }
}
