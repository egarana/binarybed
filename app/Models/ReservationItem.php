<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReservationItem extends Model
{
    /**
     * Status constants for item lifecycle.
     */
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Pricing type constants.
     */
    public const PRICING_PER_NIGHT = 'night';
    public const PRICING_PER_PERSON = 'person';
    public const PRICING_PER_HOUR = 'hourly';
    public const PRICING_FLAT = 'flat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'reservable_type',
        'reservable_id',
        'rate_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'quantity',
        // Granular Snapshotting
        'resource_name',
        'resource_type_label',
        'resource_description',
        'rate_name',
        'rate_description',
        'price_type',
        'rate_price',
        'currency',
        'line_total',
        'status',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'quantity' => 1,
        'currency' => 'IDR',
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rate_price' => 'integer',
            'line_total' => 'integer',
            'quantity' => 'integer',
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
     * Get the reservable resource (Unit or Activity).
     */
    public function reservable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the rate used for this item.
     */
    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }

    /**
     * Check if item is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if item is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Calculate line total based on pricing type.
     * Formula: quantity × rate_price
     *
     * @return int
     */
    public function calculateLineTotal(): int
    {
        $quantity = $this->quantity ?? 1;
        $ratePrice = $this->rate_price ?? 0;

        return $quantity * $ratePrice;
    }
}
