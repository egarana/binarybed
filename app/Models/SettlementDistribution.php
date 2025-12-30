<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementDistribution extends Model
{
    /**
     * Recipient types.
     */
    public const RECIPIENT_MERCHANT = 'merchant';
    public const RECIPIENT_PARTNER = 'partner';
    public const RECIPIENT_PLATFORM = 'platform';

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'reservation_item_id',
        'recipient_type',
        'recipient_id',
        'amount',
        'currency',
        'status',
        'disbursement_id',
        'disbursed_at',
        'snapshot',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'snapshot' => 'array',
            'disbursed_at' => 'datetime',
        ];
    }

    /**
     * Get the parent reservation.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the parent reservation item.
     */
    public function reservationItem(): BelongsTo
    {
        return $this->belongsTo(ReservationItem::class);
    }

    /**
     * Check if this is a merchant distribution.
     */
    public function isMerchant(): bool
    {
        return $this->recipient_type === self::RECIPIENT_MERCHANT;
    }

    /**
     * Check if this is a partner distribution.
     */
    public function isPartner(): bool
    {
        return $this->recipient_type === self::RECIPIENT_PARTNER;
    }

    /**
     * Check if this is a platform distribution.
     */
    public function isPlatform(): bool
    {
        return $this->recipient_type === self::RECIPIENT_PLATFORM;
    }

    /**
     * Check if disbursement is needed (not platform).
     */
    public function needsDisbursement(): bool
    {
        return !$this->isPlatform() && $this->status === self::STATUS_PENDING;
    }

    /**
     * Mark as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark as completed with disbursement ID.
     */
    public function markAsCompleted(string $disbursementId): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'disbursement_id' => $disbursementId,
            'disbursed_at' => now(),
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(?string $error = null): void
    {
        $snapshot = $this->snapshot ?? [];
        $snapshot['error'] = $error;

        $this->update([
            'status' => self::STATUS_FAILED,
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * Scope to pending distributions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to distributions needing disbursement.
     */
    public function scopeNeedsDisbursement($query)
    {
        return $query->pending()->whereIn('recipient_type', [
            self::RECIPIENT_MERCHANT,
            self::RECIPIENT_PARTNER,
        ]);
    }
}
