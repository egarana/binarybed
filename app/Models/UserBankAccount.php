<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class UserBankAccount extends Model
{
    use CentralConnection;

    /**
     * Common Indonesian bank codes.
     */
    public const BANK_BCA = 'BCA';
    public const BANK_MANDIRI = 'MANDIRI';
    public const BANK_BNI = 'BNI';
    public const BANK_BRI = 'BRI';
    public const BANK_CIMB = 'CIMB';
    public const BANK_PERMATA = 'PERMATA';
    public const BANK_DANAMON = 'DANAMON';
    public const BANK_BSI = 'BSI';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'global_id',
        'bank_code',
        'account_number',
        'account_holder_name',
        'is_primary',
        'is_verified',
        'xendit_beneficiary_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this bank account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'global_id', 'global_id');
    }

    /**
     * Scope to primary accounts only.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to verified accounts only.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Get masked account number for display.
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $length = strlen($this->account_number);
        if ($length <= 4) {
            return $this->account_number;
        }

        return str_repeat('*', $length - 4) . substr($this->account_number, -4);
    }

    /**
     * Get available bank options.
     */
    public static function getBankOptions(): array
    {
        return [
            self::BANK_BCA => 'Bank Central Asia (BCA)',
            self::BANK_MANDIRI => 'Bank Mandiri',
            self::BANK_BNI => 'Bank Negara Indonesia (BNI)',
            self::BANK_BRI => 'Bank Rakyat Indonesia (BRI)',
            self::BANK_CIMB => 'CIMB Niaga',
            self::BANK_PERMATA => 'Bank Permata',
            self::BANK_DANAMON => 'Bank Danamon',
            self::BANK_BSI => 'Bank Syariah Indonesia (BSI)',
        ];
    }
}
