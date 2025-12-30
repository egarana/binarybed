<?php

namespace App\Jobs;

use App\Models\SettlementDistribution;
use App\Models\UserBankAccount;
use App\Services\XenditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSettlementDisbursement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SettlementDistribution $distribution
    ) {}

    /**
     * Execute the job.
     */
    public function handle(XenditService $xendit): void
    {
        // Skip if already processed or platform
        if (!$this->distribution->needsDisbursement()) {
            return;
        }

        // Mark as processing
        $this->distribution->markAsProcessing();

        try {
            // Get bank account for recipient
            $bankAccount = $this->getBankAccount();

            if (!$bankAccount) {
                $this->distribution->markAsFailed('No bank account configured for recipient');
                Log::warning('Settlement disbursement failed: No bank account', [
                    'distribution_id' => $this->distribution->id,
                    'recipient_type' => $this->distribution->recipient_type,
                    'recipient_id' => $this->distribution->recipient_id,
                ]);
                return;
            }

            // Create disbursement via Xendit
            $result = $xendit->createDisbursement(
                externalId: 'SETTLEMENT-' . $this->distribution->id,
                bankCode: $bankAccount->bank_code,
                accountNumber: $bankAccount->account_number,
                accountHolderName: $bankAccount->account_holder_name,
                amount: $this->distribution->amount,
                description: $this->getDescription()
            );

            if ($result['success']) {
                $this->distribution->markAsCompleted($result['disbursement_id']);
                Log::info('Settlement disbursement successful', [
                    'distribution_id' => $this->distribution->id,
                    'disbursement_id' => $result['disbursement_id'],
                ]);
            } else {
                $this->distribution->markAsFailed($result['error'] ?? 'Unknown error');
                Log::error('Settlement disbursement failed', [
                    'distribution_id' => $this->distribution->id,
                    'error' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            $this->distribution->markAsFailed($e->getMessage());
            Log::error('Settlement disbursement exception', [
                'distribution_id' => $this->distribution->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw for retry
        }
    }

    /**
     * Get bank account for the recipient.
     */
    private function getBankAccount(): ?UserBankAccount
    {
        if ($this->distribution->isMerchant()) {
            // TODO: Implement tenant bank account lookup
            // For now, return null - merchant disbursement needs separate implementation
            return null;
        }

        // Partner - lookup by global_id
        return UserBankAccount::where('global_id', $this->distribution->recipient_id)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get description for disbursement.
     */
    private function getDescription(): string
    {
        $snapshot = $this->distribution->snapshot ?? [];
        $reservationCode = $snapshot['reservation']['code'] ?? 'N/A';
        $type = ucfirst($this->distribution->recipient_type);

        return "{$type} settlement - Reservation #{$reservationCode}";
    }
}
