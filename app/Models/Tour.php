<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Tour extends Model
{
    use Sluggable;

    protected $fillable = [
        'slug', 'name_en', 'name_ar', 'image_url',
        'location', 'tag_name', 'tag_color_class',
        'description_en', 'description_ar', 'is_active',
    ];

    

    protected $casts = [
        'is_active' => 'boolean',
        'base_seats' => 'integer',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name_en'
            ]
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function pricingTiers()
    {
        return $this->hasMany(TourPricingTier::class);
    }

    public function departures()
    {
        return $this->hasMany(TourDeparture::class)->orderBy('departure_time');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}