<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Unit extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'description',
        'max_guests',
        'bedroom_count',
        'bathroom_count',
        'view',
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
            'max_guests' => 'integer',
            'bedroom_count' => 'integer',
            'bathroom_count' => 'integer',
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
            ->withPivot(['assigned_at', 'role', 'commission_split', 'is_protected'])
            ->withTimestamps();
    }

    /**
     * Get the commission config for this unit
     */
    public function commissionConfig(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(ResourceCommissionConfig::class, 'resourceable');
    }

    /**
     * Get all features assigned to this unit
     */
    public function features(): MorphToMany
    {
        return $this->morphToMany(
            ResourceFeature::class,  // Related model dari tenant database (synced from central)
            'featureable',           // Polymorphic name
            'resource_features',     // Pivot table
            'featureable_id',        // foreignPivotKey - foreign key untuk Unit di pivot
            'feature_id',            // relatedPivotKey - foreign key untuk ResourceFeature  
            'id',                    // parentKey - primary key di Unit table
            'feature_id'             // relatedKey - feature_id di ResourceFeature table (bukan id!)
        )
            ->withPivot(['assigned_at', 'order', 'category'])
            ->withTimestamps()
            ->orderBy('resource_features.order');
    }

    /**
     * Get all rates for this unit
     */
    public function rates(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Rate::class, 'rateable');
    }

    /**
     * Register the media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');  // Changed from 'image' to 'images', removed singleFile()
    }

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = [];
}
