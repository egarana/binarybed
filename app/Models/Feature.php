<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\LogsModelActivity;

class Feature extends Model
{
    /** @use HasFactory<\Database\Factories\FeatureFactory> */
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'name',
        'icon',
        'value',
    ];
}
