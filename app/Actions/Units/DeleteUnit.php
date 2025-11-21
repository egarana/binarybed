<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Repositories\UnitRepository;

class DeleteUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository
    ) {}

    /**
     * Delete a unit in the specified tenant's database.
     *
     * @param string $tenantId
     * @param string $slug
     * @return void
     */
    public function execute(string $tenantId, string $slug): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            $this->unitRepository->delete($unit);
        });
    }
}
