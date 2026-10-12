<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
class TourDeparture extends Model
{
    use SoftDeletes;
    protected $fillable = ['tour_id', 'departure_time', 'departure_date', 'return_time', 'is_active'];
    protected $casts = [
        'departure_time' => 'datetime:H:i', 
        'departure_date' => 'date', 
        'return_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    public function formattedDepartureTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->departure_time ? $this->departure_time->format('g:i A') : '09:00 AM'
        );
    }

    public function formattedReturnTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->return_time ? $this->return_time->format('g:i A') : 'TBD'
        );
    }

    public function tour()
    {
       return $this->belongsTo(Tour::class)->withTrashed();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'tour_departure_vehicle')->withTimestamps();
    }

   
}