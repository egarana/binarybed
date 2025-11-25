<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ResourceUser extends Pivot
{
    protected $table = 'resource_users';
    
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'resourceable_type',
        'resourceable_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserTenant::class);
    }

    /**
     * Get the resource (polymorphic)
     */
    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }
}