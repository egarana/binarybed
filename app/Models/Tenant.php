<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'data',
    ];

    /**
     * Pastikan 'domain' muncul di hasil JSON.
     */
    protected $appends = ['domain'];

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
}