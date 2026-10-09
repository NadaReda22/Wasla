<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
   protected $fillable = [
    'area_id', 'name_en', 'name_ar', 'keywords', 'latitude', 'longitude', 'extra_transfer_fee', 'is_active',
];



    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}