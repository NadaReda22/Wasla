<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function tourDepartures()
    {
        return $this->belongsToMany(TourDeparture::class, 'tour_departure_vehicle')->withTimestamps();
    }

  
  }