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
            }

            return $this->tenantRepository->findWithDomains($tenant->id);
        });
    }
}