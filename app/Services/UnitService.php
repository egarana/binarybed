<?php

namespace App\Services;

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
    use HasCrossTenantsQuery;

    public function __construct(
        protected UnitRepository $unitRepository,
        protected CreateUnit $createUnit,
        protected UpdateUnit $updateUnit,
        protected DeleteUnit $deleteUnit,
        protected FindUnitByTenantAndSlug $findUnitByTenantAndSlug
    ) {}

    /**
     * Get the model class for cross-tenant queries.
     */
    protected function getCrossTenantsModelClass(): string
    {
        return Unit::class;
    }

    /**
     * Optional: Specify which columns to select from units table.
     * Override to customize. Default is ['*'] which selects all columns.
     */
    protected function getCrossTenantsColumns(): array
    {
        return ['id', 'name', 'slug', 'created_at', 'updated_at'];
    }

    public function getAllPaginated(Request $request)
    {
        return $this->unitRepository->getAllPaginated($request);
    }

    public function getForEdit(string $tenantId, string $slug): array
    {
        $unit = $this->findUnitByTenantAndSlug->execute($tenantId, $slug);

        return [
            
        ];
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