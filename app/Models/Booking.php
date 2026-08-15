<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    // Cast dates to proper Carbon instances
    protected $casts = [
        'booking_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function departure()
    {
        return $this->belongsTo(TourDeparture::class, 'tour_departure_id');
    }

    public function pricingTier()
    {
        return $this->belongsTo(TourPricingTier::class, 'tour_pricing_tier_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Round Trip Relations
    public function parentBooking()
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    public function returnBooking()
    {
        return $this->hasOne(Booking::class, 'parent_booking_id');
    }
}