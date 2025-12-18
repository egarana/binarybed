<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;
use Illuminate\Validation\ValidationException;

class DeleteRateFromResource
{
    use HandlesTenancy;

    /**
     * Delete a rate from a resource.
     */
    public function execute(string $tenantId, int $rateId): void
    {
        $this->executeInTenantContext($tenantId, function () use ($rateId) {
            $rate = Rate::findOrFail($rateId);

            if ($rate->is_default) {
                throw ValidationException::withMessages([
                    'rate' => 'Cannot delete the default rate. You can edit it but not delete.',
                ]);
            }

            $rate->delete();
        });
    }
}
