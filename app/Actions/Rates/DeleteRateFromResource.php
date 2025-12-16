<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;

class DeleteRateFromResource
{
    use HandlesTenancy;

    /**
     * Delete a rate from a resource.
     *
     * @param string $tenantId
     * @param int $rateId
     * @return void
     */
    public function execute(string $tenantId, int $rateId): void
    {
        $this->executeInTenantContext($tenantId, function () use ($rateId) {
            $rate = Rate::findOrFail($rateId);
            $rate->delete();
        });
    }
}
