<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;

/**
 * DeleteTenantAction
 * ------------------
 * Menghapus tenant dan domainnya tanpa menghapus database tenant.
 */
class DeleteTenant
{
    public function execute(Tenant $tenant): void
    {
        // Hapus semua domain terkait
        $tenant->domains()->delete();

        // Nonaktifkan hook drop DB dari Stancl sementara
        static::disableTenantDatabaseDeletion();

        // Hapus tenant record-nya (hanya di central DB)
        $tenant->forceDelete();
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
