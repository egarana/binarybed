<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use App\Repositories\TenantRepository;
use App\Repositories\DomainRepository;

/**
 * DeleteTenantAction
 * ------------------
 * Menghapus tenant dan domainnya tanpa menghapus database tenant.
 */
class DeleteTenant
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected DomainRepository $domainRepository
    ) {}

    public function execute(Tenant $tenant): void
    {
        // Hapus semua domain terkait melalui repository
        $this->domainRepository->deleteByTenantId($tenant->id);

        // Nonaktifkan hook drop DB dari Stancl sementara
        static::disableTenantDatabaseDeletion();

        // Hapus tenant record-nya (hanya di central DB) melalui repository
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