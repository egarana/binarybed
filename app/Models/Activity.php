<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Activity extends Model
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
     * Get all users assigned to this activity
     */
    public function users(): MorphToMany
    {
        return $this->morphToMany(
            UserTenant::class,     // Related model
            'resourceable',        // Polymorphic name
            'resource_users',      // Pivot table
            'resourceable_id',     // foreignPivotKey - foreign key untuk Activity di pivot
            'global_id',           // relatedPivotKey - foreign key untuk User di pivot
            'id',                  // parentKey - primary key di Activity table
            'global_id'            // relatedKey - primary key di UserTenant table (global_id)
        )
            ->withPivot(['assigned_at', 'role'])
            ->withTimestamps();
    }

    /**
     * Get all features assigned to this activity
     */
    public function features(): MorphToMany
    {
        return $this->morphToMany(
            ResourceFeature::class,  // Related model dari tenant database (synced from central)
            'featureable',           // Polymorphic name
            'resource_features',     // Pivot table
            'featureable_id',        // foreignPivotKey - foreign key untuk Activity di pivot
            'feature_id',            // relatedPivotKey - foreign key untuk ResourceFeature  
            'id',                    // parentKey - primary key di Activity table
            'feature_id'             // relatedKey - feature_id di ResourceFeature table (bukan id!)
        )
            ->withPivot(['assigned_at', 'order'])
            ->withTimestamps()
            ->orderBy('resource_features.order');
    }

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = [];
}
