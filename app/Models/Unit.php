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
        return $this->morphToMany(UserTenant::class, 'resourceable', 'resource_users')
            ->withPivot(['role', 'commission', 'assigned_at'])
            ->withTimestamps();
    }
}
