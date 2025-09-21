<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\LogsModelActivity;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'rate_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'check_in',
        'check_out',
        'booked_on',
        'sort_order',
    ];

    protected $casts = [
        'phone' => 'json',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}
