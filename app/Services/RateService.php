<?php

namespace App\Services;

use App\Actions\Rates\CreateRate;
use App\Actions\Rates\DeleteRate;
use App\Actions\Rates\FindRateByTenantAndSlug;
use App\Actions\Rates\UpdateRate;
use App\Models\Rate;
use App\Repositories\RateRepository;
use Illuminate\Http\Request;

class RateService
{
    public function __construct(
        protected RateRepository $rateRepository,
        protected CreateRate $createRate,
        protected UpdateRate $updateRate,
        protected DeleteRate $deleteRate,
        protected FindRateByTenantAndSlug $findRateByTenantAndSlug
    ) {}

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        return $this->rateRepository->getAllFromAllTenantsPaginated($request);
    }

    public function getForEdit(string $tenantId, string $resourceSlug, string $rateSlug): array
    {
        return $this->findRateByTenantAndSlug->execute($tenantId, $resourceSlug, $rateSlug);
    }

    public function create(array $data): array
    {
        return $this->createRate->execute($data);
    }

    public function update(string $tenantId, string $resourceSlug, string $rateSlug, array $data): Rate
    {
        return $this->updateRate->execute($tenantId, $resourceSlug, $rateSlug, $data);
    }

    public function delete(string $tenantId, string $resourceSlug, string $rateSlug): void
    {
        $this->deleteRate->execute($tenantId, $resourceSlug, $rateSlug);
    }
}
