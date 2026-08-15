<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
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
    ];

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }
}