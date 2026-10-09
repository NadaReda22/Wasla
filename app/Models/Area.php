<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'name_ru',
        'latitude',
        'longitude',
        'is_in_free_zone',
        'is_active',
        'color',

        
    ];

    protected $casts = [
        'is_in_free_zone' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'polygon_coordinates' => 'array',
    ];

   
    /**
     * Scope to only get active areas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the hotels associated with the area.
     */
    public function hotels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}