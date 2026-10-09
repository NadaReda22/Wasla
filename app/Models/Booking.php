<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    // Cast dates to proper Carbon instances
    protected $casts = [
        'booking_date' => 'date',
        'is_cash_on_board_paid' => 'boolean',
        'is_distribution_ensured' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class)->withTrashed();
    }


    public function departure()
    {
        return $this->belongsTo(TourDeparture::class, 'tour_departure_id')->withTrashed();
    }

    public function pricingTier()
    {
        return $this->belongsTo(TourPricingTier::class, 'tour_pricing_tier_id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function outboundVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'outbound_vehicle_id');
    }

    public function returnVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'return_vehicle_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    protected static function booted(): void
{
    static::updating(function (Booking $b) {
        if (!$b->getOriginal('is_distribution_ensured')) return;

        $allowed = ['is_cash_on_board_paid', 'whatsapp_status', 'updated_at'];
        if (array_diff(array_keys($b->getDirty()), $allowed)) {
            throw new \DomainException('This booking is locked.');
        }
    });

    static::deleting(function (Booking $b) {
        if ($b->is_distribution_ensured) throw new \DomainException('This booking is locked.');
    });
}

}