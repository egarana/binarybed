<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Unit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        //
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }

    /**
     * Get all users assigned to this unit
     */
    public function users(): MorphToMany
    {
        return $this->morphToMany(
            UserTenant::class,     // Related model
            'resourceable',        // Polymorphic name
            'resource_users',      // Pivot table
            'resourceable_id',     // foreignPivotKey - foreign key untuk Unit di pivot
            'global_id',           // relatedPivotKey - foreign key untuk User di pivot
            'id',                  // parentKey - primary key di Unit table
            'global_id'            // relatedKey - primary key di UserTenant table (global_id)
        )
            ->withPivot(['assigned_at', 'role'])
            ->withTimestamps();
    }
}
