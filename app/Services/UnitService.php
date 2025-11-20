<?php

namespace App\Services;

use App\Actions\Units\CreateUnit;
use App\Actions\Units\DeleteUnit;
use App\Actions\Units\FindUnitByTenantAndSlug;
use App\Actions\Units\UpdateUnit;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Http\Request;

class UnitService
{
    public function __construct(
        protected UnitRepository $repository,
        protected CreateUnit $createUnit,
        protected UpdateUnit $updateUnit,
        protected DeleteUnit $deleteUnit,
        protected FindUnitByTenantAndSlug $findUnitByTenantAndSlug
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->repository->getAllPaginated($request);
    }

    public function findByTenantAndSlug(string $tenantId, string $slug): array
    {
        return $this->findUnitByTenantAndSlug->execute($tenantId, $slug);
    }

    public function create(array $data): Unit
    {
        return $this->createUnit->execute($data);
    }

    public function update(string $tenantId, string $slug, array $data): Unit
    {
        return $this->updateUnit->execute($tenantId, $slug, $data);
    }

    public function delete(string $tenantId, string $slug): void
    {
        $this->deleteUnit->execute($tenantId, $slug);
    }
}
