<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use App\Repositories\TenantRepository;
use App\Repositories\DomainRepository;

class DeleteTenant
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected DomainRepository $domainRepository
    ) {}

    public function execute(Tenant $tenant): void
    {
        $this->domainRepository->deleteByTenantId($tenant->id);

        static::disableTenantDatabaseDeletion();

        $this->tenantRepository->forceDelete($tenant);
    }

    protected static function disableTenantDatabaseDeletion(): void
    {
        // Lepas event bawaan Stancl yang memicu DROP DATABASE
        Tenant::flushEventListeners();

        // Re-register event penting selain drop DB (opsional)
        Tenant::creating(function ($model) {
            $model->created_at = now();
        });
    }
}