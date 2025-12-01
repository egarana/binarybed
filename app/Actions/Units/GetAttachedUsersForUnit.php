<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Http\Request;

class GetAttachedUsersForUnit
{
    use HandlesTenancy;

    public function __construct(
        protected UnitRepository $unitRepository,
        protected Request $request
    ) {}

    /**
     * Get all users attached to a unit with filtering and sorting support.
     *
     * @param string $tenantId
     * @param string $slug
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(string $tenantId, string $slug): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->executeInTenantContext($tenantId, function () use ($slug) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            return $this->unitRepository->getAttachedUsersPaginated($unit, $this->request);
        });
    }
}
