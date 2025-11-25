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
        'global_user_id',
        'resourceable_type',
        'resourceable_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the user that owns this assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            UserTenant::class,
            'global_user_id',  // foreign key di resource_users table
            'id'               // owner key di users table (primary key)
        );
    }

    /**
     * Get the resource (polymorphic)
     */
    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }
}