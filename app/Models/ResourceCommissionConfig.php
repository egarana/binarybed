<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ResourceCommissionConfig extends Model
{
    /**
     * Commission types.
     */
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'resourceable_type',
        'resourceable_id',
        'commission_type',
        'commission_percentage',
        'commission_fixed',
        'currency',
        'fixed_unit', // Kept for backward compatibility but not used in calculation
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
            'commission_fixed' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent resourceable model (Unit or Activity).
     */
    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to active configs only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this is a percentage-based commission.
     */
    public function isPercentage(): bool
    {
        return $this->commission_type === self::TYPE_PERCENTAGE;
    }

    /**
     * Check if this is a fixed commission.
     */
    public function isFixed(): bool
    {
        return $this->commission_type === self::TYPE_FIXED;
    }

    /**
     * Check if the resource is a Unit.
     */
    public function isUnit(): bool
    {
        return $this->resourceable_type === Unit::class;
    }

    /**
     * Check if the resource is an Activity.
     */
    public function isActivity(): bool
    {
        return $this->resourceable_type === Activity::class;
    }

    /**
     * Calculate commission for a reservation item.
     *
     * Multiplier logic:
     * - Unit: nights × quantity (e.g., 3 nights × 2 villas = 6)
     * - Activity: quantity (e.g., 4 persons = 4)
     *
     * @param int $lineTotal The line total amount
     * @param int $quantity The quantity (persons, items, units booked)
     * @param int $nights Number of nights (for Unit-based)
     * @return int The commission amount
     */
    public function calculateCommission(
        int $lineTotal,
        int $quantity = 1,
        int $nights = 1
    ): int {
        if ($this->isPercentage()) {
            return (int) ($lineTotal * ($this->commission_percentage / 100));
        }

        // Fixed commission - multiplier based on resource type
        $multiplier = $this->isUnit()
            ? $nights * $quantity  // Unit: nights × quantity
            : $quantity;           // Activity: quantity only

        return $this->commission_fixed * $multiplier;
    }
}
