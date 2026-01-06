<?php

namespace App\Services;

use App\Actions\Units\AttachUserToUnit;
use App\Actions\Units\CreateUnit;
use App\Actions\Units\DeleteUnit;
use App\Actions\Units\DetachUserFromUnit;
use App\Actions\Units\FindUnitByTenantAndSlug;
use App\Actions\Units\FindUnitForCommission;
use App\Actions\Units\GetAttachedUsersForUnit;
use App\Actions\Units\UpdateUnit;
use App\Actions\Units\UpdateUserUnitRole;
use App\HasCrossTenantsQuery;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitService
{
    public function __construct(
        protected UnitRepository $unitRepository,
        protected CreateUnit $createUnit,
        protected UpdateUnit $updateUnit,
        protected DeleteUnit $deleteUnit,
        protected FindUnitByTenantAndSlug $findUnitByTenantAndSlug,
        protected FindUnitForCommission $findUnitForCommission,
        protected AttachUserToUnit $attachUserToUnit,
        protected DetachUserFromUnit $detachUserFromUnit,
        protected GetAttachedUsersForUnit $getAttachedUsersForUnit,
        protected UpdateUserUnitRole $updateUserUnitRole
    ) {}

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        return $this->unitRepository->getAllFromAllTenantsPaginated($request);
    }

    public function getForEdit(string $tenantId, string $slug): array
    {
        return $this->findUnitByTenantAndSlug->execute($tenantId, $slug);
    }

    public function getForCommission(string $tenantId, string $slug): array
    {
        return $this->findUnitForCommission->execute($tenantId, $slug);
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

    public function detachUserFromUnit(string $tenantId, string $slug, string $userGlobalId): void
    {
        $this->detachUserFromUnit->execute($tenantId, $slug, $userGlobalId);
    }

    public function updateUserUnitRole(string $tenantId, string $slug, string $userGlobalId, array $data): void
    {
        $this->updateUserUnitRole->execute($tenantId, $slug, $userGlobalId, $data);
    }

    public function getAttachedUsers(string $tenantId, string $slug): LengthAwarePaginator
    {
        return $this->getAttachedUsersForUnit->execute($tenantId, $slug);
    }

    public function getForFeatures(string $tenantId, string $slug): array
    {
        return $this->findUnitByTenantAndSlug->executeForFeatures($tenantId, $slug);
    }

    public function syncFeatures(string $tenantId, string $slug, array $featuresData): void
    {
        $this->updateUnit->syncFeatures($tenantId, $slug, $featuresData);
    }
}
