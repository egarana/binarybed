<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Feature extends Model
{
    use HasFactory, CentralConnection;

    protected $guarded = [];
    protected $table = 'features';

    protected $fillable = [
        'name',
        'value',
        'description',
        'icon',
        'category',
    ];

    /**
     * Category constants
     */
    const CATEGORY_AMENITY = 'amenity';
    const CATEGORY_EQUIPMENT = 'equipment';
    const CATEGORY_EXCLUSION = 'exclusion';
    const CATEGORY_FACILITY = 'facility';
    const CATEGORY_INCLUSION = 'inclusion';
    const CATEGORY_REQUIREMENT = 'requirement';

    /**
     * Get all available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_AMENITY => 'Amenity',
            self::CATEGORY_EQUIPMENT => 'Equipment',
            self::CATEGORY_EXCLUSION => 'Exclusion',
            self::CATEGORY_FACILITY => 'Facility',
            self::CATEGORY_INCLUSION => 'Inclusion',
            self::CATEGORY_REQUIREMENT => 'Requirement',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
