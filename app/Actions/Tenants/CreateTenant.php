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

    /**
     * Jalankan proses pembuatan tenant baru.
     *
     * @param  array  $data
     * @return Tenant
     */
    public function execute(array $data): Tenant
    {
        return tenancy()->central(function () use ($data) {
            $domain = $data['domain'];
            unset($data['domain']);

            $tenant = $this->tenantRepository->create($data);

            $this->domainRepository->create($domain, $tenant->id);

            return $tenant;
        });
    }
}