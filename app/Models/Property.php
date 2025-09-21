<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\LogsModelActivity;

class Property extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'name',
        'domain',
        'slug',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Property $property) {
            // Detach users on delete
            $property->users()->detach();
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
