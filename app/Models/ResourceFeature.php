<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ResourceFeature model - represents synced features in tenant database
 * Synced from central Feature model
 */
class ResourceFeature extends Model
{
    protected $table = 'features'; // Explicit table name to avoid conflict with pivot table
    protected $guarded = [];

    protected $fillable = [
        'feature_id',
        'name',
        'value',
        'description',
        'icon',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the central feature
     */
    public function centralFeature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }
}
