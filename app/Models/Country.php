<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'iso_alpha2',
        'name',
        'dialing_code',
    ];
}
