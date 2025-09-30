<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\LogsModelActivity;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'reservation_code',
        'unit_id',
        'rate_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'check_in',
        'check_out',
        'nights',
        'guests',
        'qty',
        'subtotal',
        'tax_total',
        'service_total',
        'extra_total',
        'total_price',
        'currency',
        'status',
        'payment_status',
        'booked_on',
        'sort_order',
        'notes',
        'source',
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

    public function reservationDetails(): HasMany
    {
        return $this->hasMany(ReservationDetail::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(ReservationSlot::class);
    }
}
