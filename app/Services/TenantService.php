<?php

namespace App\Services;

use App\Models\Tenant;
use App\Repositories\TenantRepository;
use App\Actions\Tenants\CreateTenant;
use App\Actions\Tenants\UpdateTenant;
use App\Actions\Tenants\DeleteTenant;
use Illuminate\Http\Request;

class TenantService
{
    public function __construct(
        protected TenantRepository $repository,
        protected CreateTenant $createTenant,
        protected UpdateTenant $updateTenant,
        protected DeleteTenant $deleteTenant,
    ) {}

    // public function getAll()
    // {
    //     return $this->repository->all();
    // }

    // public function getById(int|string $id): Tenant
    // {
    //     return $this->repository->find($id);
    // }

    public function getPaginated(Request $request)
    {
        return $this->repository->paginate($request);
    }

    public function create(array $data): Tenant
    {
        return $this->createTenant->execute(
            id: $data['id'] ?? null,
            name: $data['name'],
            domain: $data['domain']
        );
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
