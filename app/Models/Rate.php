<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rateable_type',
        'rateable_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'price_type',
        'is_active',
        'is_default',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'price' => 0,
        'currency' => 'IDR',
        'price_type' => null,
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the parent rateable model (Unit, Activity, etc.)
     */
    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }
}
