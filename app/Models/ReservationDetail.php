<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'date',
        'rate_id',
        'qty',
        'base_price',
        'tax_amount',
        'service_fee',
        'extra_charge',
        'total_price',
        'currency',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
