<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key', '');
        $this->baseUrl = config('services.xendit.base_url', 'https://api.xendit.co');
    }

    /**
     * Create a disbursement to bank account.
     *
     * @param string $externalId Unique ID from your system
     * @param string $bankCode Bank code (BCA, MANDIRI, etc.)
     * @param string $accountNumber Account number
     * @param string $accountHolderName Account holder name
     * @param int $amount Amount in IDR
     * @param string $description Description
     * @return array Result with success status
     */
    public function createDisbursement(
        string $externalId,
        string $bankCode,
        string $accountNumber,
        string $accountHolderName,
        int $amount,
        string $description = ''
    ): array {
        // Skip if no API key configured
        if (empty($this->secretKey)) {
            Log::warning('Xendit disbursement skipped: No API key configured');
            return [
                'success' => false,
                'error' => 'Xendit API key not configured',
            ];
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/disbursements", [
                    'external_id' => $externalId,
                    'bank_code' => $bankCode,
                    'account_holder_name' => $accountHolderName,
                    'account_number' => $accountNumber,
                    'amount' => $amount,
                    'description' => $description,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'disbursement_id' => $data['id'],
                    'external_id' => $data['external_id'],
                    'status' => $data['status'],
                    'amount' => $data['amount'],
                ];
            }

            $error = $response->json();
            Log::error('Xendit disbursement API error', [
                'external_id' => $externalId,
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['message'] ?? 'Unknown API error',
                'error_code' => $error['error_code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Xendit disbursement exception', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available bank disbursement channels.
     *
     * @return array
     */
    public function getDisbursementChannels(): array
    {
        if (empty($this->secretKey)) {
            return [];
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/available_disbursements_banks");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Xendit get channels exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get disbursement status by ID.
     *
     * @param string $disbursementId
     * @return array|null
     */
    public function getDisbursementStatus(string $disbursementId): ?array
    {
        if (empty($this->secretKey)) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/disbursements/{$disbursementId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Xendit get status exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
