<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\LogsModelActivity;

class Unit extends Model
{
    /** @use HasFactory<\Database\Factories\UnitFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'qty',
        'features',
    ];

    protected $casts = [
        // 'features' => 'json',
        'features' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Unit $unit) {
            $unit->rates()->delete();
            $unit->availabilities()->delete();
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function standardRate(): HasOne
    {
        // New relationship: only the "Standard Rate"
        return $this->hasOne(Rate::class)->where('name', 'Standard Rate');
    }
}
