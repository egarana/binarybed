<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use App\Repositories\TenantRepository;
use App\Repositories\DomainRepository;
use Illuminate\Support\Facades\DB;

class UpdateTenant
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected DomainRepository $domainRepository
    ) {}

    public function execute(Tenant $tenant, array $data): Tenant
    {
        return DB::transaction(function () use ($tenant, $data) {
            $domain = $data['domain'] ?? null;
            if ($domain) {
                unset($data['domain']);
            }

            // Extract resource_routes before updating
            $resourceRoutes = null;
            $hasResourceRoutes = array_key_exists('resource_routes', $data);
            if ($hasResourceRoutes) {
                $resourceRoutes = $data['resource_routes'];
                unset($data['resource_routes']);
            }

            if (!empty($data)) {
                $tenant = $this->tenantRepository->update($tenant, $data);
            }

            if ($domain) {
                $domainRecord = $this->domainRepository->findFirstByTenantId($tenant->id);

                if ($domainRecord) {
                    $this->domainRepository->update($domainRecord, $domain);
                } else {
                    $this->domainRepository->create($domain, $tenant->id);
                }

                // Touch tenant to update updated_at when domain changes
                $tenant->touch();
            }

            // Update resource_routes as dynamic attribute (stored in 'data' JSON column)
            if ($hasResourceRoutes) {
                $tenant->resource_routes = $resourceRoutes ?? [];
                $tenant->save();
            }

            return $this->tenantRepository->findWithDomains($tenant->id);
        });
    }
}
