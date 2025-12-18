<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;
use App\Repositories\RateRepository;
use Illuminate\Validation\ValidationException;

class DeleteRate
{
    use HandlesTenancy;

    public function __construct(
        protected RateRepository $rateRepository
    ) {}

    /**
     * Delete a rate in the specified tenant's database.
     */
    public function execute(string $tenantId, string $slug): void
    {
        $this->executeInTenantContext($tenantId, function () use ($slug) {
            $rate = Rate::where('slug', $slug)->firstOrFail();

            if ($rate->is_default) {
                throw ValidationException::withMessages([
                    'rate' => 'Cannot delete the default rate. You can edit it but not delete.',
                ]);
            }

            $this->rateRepository->delete($rate);
        });
    }
}
