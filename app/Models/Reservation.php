<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    /**
     * Status constants for reservation lifecycle.
     */
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_NO_SHOW = 'NO_SHOW';

    /**
     * All available statuses.
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'notes',
        'cancellation_reason',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'subtotal' => 0,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 0,
        'currency' => 'IDR',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    /**
     * Get the guest phone attribute.
     * Handles double-encoded legacy data.
     */
    public function getGuestPhoneAttribute($value): ?array
    {
        if ($value === null) {
            return null;
        }

        // If it's already an array, return as-is
        if (is_array($value)) {
            return $value;
        }

        // Try to decode JSON
        $decoded = json_decode($value, true);

        // If decoded result is still a string (double-encoded), decode again
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return $decoded;
    }

    /**
     * Set the guest phone attribute.
     * Ensures data is stored as proper JSON.
     */
    public function setGuestPhoneAttribute($value): void
    {
        // If it's a string (JSON from frontend), decode it first
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        // Store as JSON-encoded string
        $this->attributes['guest_phone'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get all items for this reservation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    /**
     * Get only active items.
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(ReservationItem::class)
            ->where('status', ReservationItem::STATUS_ACTIVE);
    }

    /**
     * Get all settlement distributions for this reservation.
     */
    public function settlementDistributions(): HasMany
    {
        return $this->hasMany(SettlementDistribution::class);
    }

    /**
     * Generate a unique reservation code.
     * Format: 10 uppercase alphanumeric characters, excluding ambiguous chars (0, O, 1, I, L).
     *
     * @return string
     */
    public static function generateUniqueCode(): string
    {
        // Characters excluding ambiguous ones: 0, O, 1, I, L
        $characters = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $length = 10;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Recalculate totals from active items.
     *
     * @return self
     */
    public function recalculateTotals(): self
    {
        $this->subtotal = $this->activeItems()->sum('line_total');
        $this->total_amount = $this->subtotal - $this->discount_amount + $this->tax_amount;

        return $this;
    }

    /**
     * Check if reservation can be modified (not completed or cancelled).
     *
     * @return bool
     */
    public function isModifiable(): bool
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Check if reservation is active (pending or confirmed).
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Get the count of active items.
     *
     * @return int
     */
    public function getActiveItemsCountAttribute(): int
    {
        return $this->activeItems()->count();
    }
}
