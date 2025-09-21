<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\LogsModelActivity;

class Rate extends Model
{
    /** @use HasFactory<\Database\Factories\RateFactory> */
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'unit_id',
        'name',        // e.g., "Standard Rate", "Weekend Special"
        'price',       // numeric price
    ];

    protected static function booted(): void
    {
        static::deleting(function (Unit $unit) {
            $unit->availabilities()->delete();
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }
}
