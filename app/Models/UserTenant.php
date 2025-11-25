<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Contracts\Syncable;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;

class UserTenant extends Authenticatable implements Syncable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, ResourceSyncing;

    protected $guarded = [];
    // public $timestamps = false;

    // Menggunakan tabel 'users' di tenant database
    protected $table = 'users';

    // Spesifikasi primary key adalah global_id, bukan id
    protected $primaryKey = 'global_id';

    // Karena global_id bukan auto-increment (diambil dari central database)
    public $incrementing = false;

    // Tipe data primary key adalah integer
    protected $keyType = 'int';

    protected $fillable = [
        'global_id',
        'name',
        'email',
    ];

    protected $hidden = [
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getGlobalIdentifierKey()
    {
        return $this->getAttribute($this->getGlobalIdentifierKeyName());
    }

    public function getGlobalIdentifierKeyName(): string
    {
        return 'global_id';
    }

    public function getCentralModelName(): string
    {
        return User::class;
    }

    public function getSyncedAttributeNames(): array
    {
        return [
            'name',
            'email',
        ];
    }

    /**
     * Get all units assigned to this user
     */
    public function units(): MorphToMany
    {
        return $this->morphedByMany(
            Unit::class,           // Related model
            'resourceable',        // Polymorphic name
            'resource_users',      // Pivot table
            'global_id',           // relatedPivotKey - foreign key di pivot table untuk user
            'id',                  // parentKey - primary key di Unit table
            'global_id',           // ownerKey - primary key di UserTenant (global_id)
            'id'                   // relatedKey - primary key di Unit table
        )
        ->withPivot(['assigned_at'])
        ->withTimestamps();
    }
}