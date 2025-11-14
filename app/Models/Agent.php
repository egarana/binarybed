<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'id',
        'code',
        'name',
    ];

    protected static function booted(): void
    {
        static::creating(function ($agent) {
            if (empty($agent->code)) {
                $agent->code = static::generateAgentCode();
            }
        });
    }

    /**
     * Generate a unique 10-character alphanumeric agent code.
     *
     * Generates a random code using uppercase letters (A-Z) and numbers (0-9).
     * Ensures uniqueness by checking against existing codes in the database.
     * Will loop until a unique code is generated.
     *
     * @return string The generated unique agent code
     *
     * @example "A3K9X2B7Q1"
     */
    public static function generateAgentCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 10;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
