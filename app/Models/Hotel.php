<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $guarded = [];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}