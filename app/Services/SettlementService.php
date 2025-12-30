<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\ResourceCommissionConfig;
use App\Models\SettlementDistribution;
use App\Jobs\ProcessSettlementDisbursement;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    /**
     * Process settlement for a completed reservation.
     * Creates distribution records for merchant, partner, and platform.
     *
     * @param Reservation $reservation
     * @return array List of created distributions
     */
    public function processReservation(Reservation $reservation): array
    {
        // Idempotency check - don't process if already done
        if ($reservation->settlementDistributions()->exists()) {
            return [];
        }

        $distributions = [];

        DB::transaction(function () use ($reservation, &$distributions) {
            foreach ($reservation->activeItems as $item) {
                $itemDistributions = $this->processItem($reservation, $item);
                $distributions = array_merge($distributions, $itemDistributions);
            }
        });

        // Dispatch disbursement jobs for distributions that need it
        foreach ($distributions as $distribution) {
            if ($distribution->needsDisbursement()) {
                ProcessSettlementDisbursement::dispatch($distribution);
            }
        }

        return $distributions;
    }

    /**
     * Process a single reservation item.
     *
     * @param Reservation $reservation
     * @param ReservationItem $item
     * @return array List of created distributions
     */
    private function processItem(Reservation $reservation, ReservationItem $item): array
    {
        $resource = $item->reservable;
        if (!$resource) {
            return [];
        }

        // Get commission config for resource
        $config = $resource->commissionConfig;
        $commissionRate = $config?->commission_percentage ?? 5.00; // Default 5%

        // Calculate total commission
        $totalCommission = $this->calculateCommission($item, $config);
        if ($totalCommission <= 0) {
            return [];
        }

        // Get merchant (tenant) and partner info
        $tenantId = tenancy()->tenant?->id;
        $partner = $this->getPartnerForResource($resource);

        // Calculate splits
        $splits = $this->calculateSplits($totalCommission, $partner);

        // Create distributions
        $distributions = [];
        $merchantAmount = $item->line_total - $totalCommission;

        // 1. Merchant distribution (tenant gets line_total - commission)
        $distributions[] = $this->createDistribution(
            $reservation,
            $item,
            SettlementDistribution::RECIPIENT_MERCHANT,
            $tenantId,
            $merchantAmount,
            [
                'type' => 'merchant',
                'tenant_id' => $tenantId,
                'line_total' => $item->line_total,
                'commission_deducted' => $totalCommission,
            ]
        );

        // 2. Partner distribution (if exists)
        if (isset($splits['partner'])) {
            $distributions[] = $this->createDistribution(
                $reservation,
                $item,
                SettlementDistribution::RECIPIENT_PARTNER,
                $partner->global_id,
                $splits['partner']['amount'],
                [
                    'type' => 'partner',
                    'global_id' => $partner->global_id,
                    'name' => $partner->name ?? null,
                    'email' => $partner->email ?? null,
                    'split_percentage' => $splits['partner']['percentage'],
                    'commission_rate' => $commissionRate,
                ]
            );
        }

        // 3. Platform distribution
        $distributions[] = $this->createDistribution(
            $reservation,
            $item,
            SettlementDistribution::RECIPIENT_PLATFORM,
            null,
            $splits['platform']['amount'],
            [
                'type' => 'platform',
                'split_percentage' => $splits['platform']['percentage'],
                'commission_rate' => $commissionRate,
            ]
        );

        return $distributions;
    }

    /**
     * Calculate commission for an item.
     *
     * @param ReservationItem $item
     * @param ResourceCommissionConfig|null $config
     * @return int Commission amount
     */
    private function calculateCommission(ReservationItem $item, ?ResourceCommissionConfig $config): int
    {
        if (!$config || !$config->is_active) {
            // Default: 5% of line_total
            return (int) ($item->line_total * 0.05);
        }

        // Calculate nights for Unit-based fixed commission
        $nights = 1;
        if ($item->start_date && $item->end_date) {
            $nights = max(1, $item->start_date->diffInDays($item->end_date));
        }

        return $config->calculateCommission(
            $item->line_total,
            $item->quantity ?? 1,
            $nights
        );
    }

    /**
     * Get partner assigned to resource.
     *
     * @param mixed $resource Unit or Activity
     * @return object|null Partner data with global_id, commission_split
     */
    private function getPartnerForResource($resource): ?object
    {
        return DB::table('resource_users')
            ->where('resourceable_type', get_class($resource))
            ->where('resourceable_id', $resource->id)
            ->where('role', 'partner')
            ->first();
    }

    /**
     * Calculate split amounts based on partner existence.
     *
     * @param int $totalCommission
     * @param object|null $partner
     * @return array Split configuration
     */
    private function calculateSplits(int $totalCommission, ?object $partner): array
    {
        // Case 1: Direct (no partner) - 100% to platform
        if (!$partner) {
            return [
                'platform' => [
                    'percentage' => 100,
                    'amount' => $totalCommission,
                ],
            ];
        }

        // Case 2: Via Partner - split based on commission_split
        $partnerSplit = $partner->commission_split ?? 70.00;
        $platformSplit = 100 - $partnerSplit;

        $partnerAmount = (int) ($totalCommission * ($partnerSplit / 100));
        $platformAmount = $totalCommission - $partnerAmount; // Ensure no rounding loss

        return [
            'partner' => [
                'percentage' => $partnerSplit,
                'amount' => $partnerAmount,
            ],
            'platform' => [
                'percentage' => $platformSplit,
                'amount' => $platformAmount,
            ],
        ];
    }

    /**
     * Create a settlement distribution record with snapshot.
     *
     * @param Reservation $reservation
     * @param ReservationItem $item
     * @param string $recipientType
     * @param string|null $recipientId
     * @param int $amount
     * @param array $recipientSnapshot
     * @return SettlementDistribution
     */
    private function createDistribution(
        Reservation $reservation,
        ReservationItem $item,
        string $recipientType,
        ?string $recipientId,
        int $amount,
        array $recipientSnapshot
    ): SettlementDistribution {
        $snapshot = [
            'reservation' => [
                'id' => $reservation->id,
                'code' => $reservation->code,
                'status' => $reservation->status,
                'completed_at' => now()->toIso8601String(),
            ],
            'item' => [
                'id' => $item->id,
                'resource_name' => $item->resource_name,
                'resource_type' => $item->resource_type_label,
                'line_total' => $item->line_total,
                'quantity' => $item->quantity,
                'rate_price' => $item->rate_price,
            ],
            'recipient' => $recipientSnapshot,
            'calculated_at' => now()->toIso8601String(),
        ];

        // Platform doesn't need disbursement - mark as processed
        $status = $recipientType === SettlementDistribution::RECIPIENT_PLATFORM
            ? SettlementDistribution::STATUS_COMPLETED
            : SettlementDistribution::STATUS_PENDING;

        return SettlementDistribution::create([
            'reservation_id' => $reservation->id,
            'reservation_item_id' => $item->id,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'amount' => $amount,
            'currency' => $reservation->currency ?? 'IDR',
            'status' => $status,
            'snapshot' => $snapshot,
        ]);
    }
}
