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
            // Extract domain from data if present
            $domain = $data['domain'] ?? null;
            if ($domain) {
                unset($data['domain']);
            }

            // Sanitize Xendit disbursement fields based on account_type
            $data = $this->sanitizeXenditFields($data);

            // Update tenant data including Xendit fields
            if (!empty($data)) {
                $tenant = $this->tenantRepository->update($tenant, $data);
            }

            // Update atau buat domain baru jika domain disediakan
            if ($domain) {
                $domainRecord = $this->domainRepository->findFirstByTenantId($tenant->id);

                if ($domainRecord) {
                    // Update domain yang sudah ada melalui repository
                    $this->domainRepository->update($domainRecord, $domain);
                } else {
                    // Buat domain baru melalui repository
                    $this->domainRepository->create($domain, $tenant->id);
                }
            }

            // Return tenant dengan relasi fresh
            return $this->tenantRepository->findWithDomains($tenant->id);
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
        // Only sanitize if account_type is being updated
        if (!isset($data['account_type'])) {
            return $data;
        }

        $accountType = $data['account_type'];

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