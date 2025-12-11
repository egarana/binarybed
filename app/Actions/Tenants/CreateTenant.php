<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use App\Repositories\TenantRepository;
use App\Repositories\DomainRepository;

class CreateTenant
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected DomainRepository $domainRepository
    ) {}

    public function execute(array $data): Tenant
    {
        return tenancy()->central(function () use ($data) {
            $domain = $data['domain'];
            unset($data['domain']);

            // Extract resource_routes before creating tenant
            $resourceRoutes = $data['resource_routes'] ?? null;
            unset($data['resource_routes']);

            $tenant = $this->tenantRepository->create($data);

            $this->domainRepository->create($domain, $tenant->id);

            // Set resource_routes as dynamic attribute (stored in 'data' JSON column)
            if ($resourceRoutes !== null) {
                $tenant->resource_routes = $resourceRoutes;
                $tenant->save();
            }

            return $tenant;
        });
    }
}
