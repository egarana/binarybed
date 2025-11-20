<?php

namespace App\Services;

use App\Actions\Tenants\CreateTenant;
use App\Actions\Tenants\DeleteTenant;
use App\Actions\Tenants\UpdateTenant;
use App\Models\Tenant;
use App\Repositories\TenantRepository;
use Illuminate\Http\Request;

class TenantService
{
    public function __construct(
        protected TenantRepository $repository,
        protected CreateTenant $createTenant,
        protected UpdateTenant $updateTenant,
        protected DeleteTenant $deleteTenant,
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->repository->getAllPaginated($request);
    }

    public function search(?string $search = null, int $limit = 10): array
    {
        return $this->repository->search($search, $limit);
    }

    public function create(array $data): Tenant
    {
        return $this->createTenant->execute($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        return $this->updateTenant->execute($tenant, $data);
    }

    public function delete(Tenant $tenant): void
    {
        $this->deleteTenant->execute($tenant);
    }
}
