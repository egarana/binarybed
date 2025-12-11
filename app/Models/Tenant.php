<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\TenantPivot;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Pastikan 'domain' dan 'resource_routes' muncul di hasil JSON.
     */
    protected $appends = ['domain', 'resource_routes'];

    /**
     * Opsional: otomatis eager load 'domains'
     * agar tidak query ulang di accessor.
     */
    protected $with = ['domains'];

    /**
     * Kolom custom Tenant bawaan BinaryBed.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'created_at',
            'updated_at',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tenant_users',
            'tenant_id',
            'global_user_id',
            'id',
            'global_id'
        )->using(TenantPivot::class);
    }

    /**
     * Accessor untuk domain utama tenant.
     * Mengambil domain pertama dari relasi 'domains'.
     */
    public function getDomainAttribute(): ?string
    {
        // Gunakan data relasi yang sudah di-load, hindari query tambahan
        return $this->relations['domains'][0]->domain
            ?? $this->domains()->first()?->domain;
    }

    /**
     * Accessor untuk resource_routes dari data JSON.
     * Stancl/Tenancy menyimpan dynamic attributes di kolom 'data'.
     */
    public function getResourceRoutesAttribute(): array
    {
        $rawData = $this->getRawOriginal('data');
        if (is_string($rawData)) {
            $decoded = json_decode($rawData, true);
            return $decoded['resource_routes'] ?? [];
        }
        return [];
    }
}
