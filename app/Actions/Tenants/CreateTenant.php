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
        // Pastikan semua operasi di central database
        return tenancy()->central(function () use ($data) {
            // Extract domain from data
            $domain = $data['domain'];
            unset($data['domain']);

            // Sanitize Xendit disbursement fields based on account_type
            $data = $this->sanitizeXenditFields($data);

            // 1. Buat tenant with all data including Xendit fields
            $tenant = $this->tenantRepository->create($data);

            // 2. Buat domain
            $this->domainRepository->create($domain, $tenant->id);

            // 3. Return tenant (TenantCreated event fired automatically by Tenant::create())
            return $tenant;
        });
    }

    /**
     * Sanitize Xendit disbursement fields based on account type.
     * Prevents data duplication by nullifying irrelevant fields.
     *
     * @param  array  $data
     * @return array
     */
    private function sanitizeXenditFields(array $data): array
    {
        $accountType = $data['account_type'] ?? 'bank_account';

        if ($accountType === 'bank_account') {
            // Clear e-wallet fields when using bank account
            $data['ewallet_type'] = null;
            $data['ewallet_phone'] = null;
        } elseif ($accountType === 'ewallet') {
            // Clear bank account fields when using e-wallet
            $data['bank_code'] = null;
            $data['bank_account_number'] = null;
            $data['bank_account_holder_name'] = null;
        }

        return $data;
    }
}