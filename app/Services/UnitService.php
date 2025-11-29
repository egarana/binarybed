<?php

namespace App\Services;

use App\Actions\Units\AttachUserToUnit;
use App\Actions\Units\CreateUnit;
use App\Actions\Units\DeleteUnit;
use App\Actions\Units\FindUnitByTenantAndSlug;
use App\Actions\Units\UpdateUnit;
use App\HasCrossTenantsQuery;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Http\Request;

class UnitService
{
    public function __construct(
        protected UnitRepository $unitRepository,
        protected CreateUnit $createUnit,
        protected UpdateUnit $updateUnit,
        protected DeleteUnit $deleteUnit,
        protected FindUnitByTenantAndSlug $findUnitByTenantAndSlug,
        protected AttachUserToUnit $attachUserToUnit
    ) {}

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        return $this->unitRepository->getAllFromAllTenantsPaginated($request);
    }

    public function getForEdit(string $tenantId, string $slug): array
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

    public function attachUserToUnit(string $tenantId, string $slug, array $data): void
    {
        $this->attachUserToUnit->execute($tenantId, $slug, $data);
    }
}
