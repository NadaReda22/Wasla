<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourPricingTier extends Model
{
    protected $fillable = [
        'tour_id', 'vehicle_type', 'trip_type', 'pax_count', 'is_base',
        'offer_name', 'total_price', 'deposit_amount', 'is_active',
    ];
 

    protected $casts = [
        'pax_count' => 'integer',
        'total_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Find the base tier (is_base = true) for the same tour, vehicle_type, trip_type.
     */
    public function baseTier()
    {
        return self::where('tour_id', $this->tour_id)
            ->where('vehicle_type', $this->vehicle_type)
            ->where('trip_type', $this->trip_type)
            ->where('is_base', true)
            ->first();
    }

    /**
     * Original price: base tier’s total_price × offer’s pax_count.
     * (Base tier always has pax_count = 1, so total_price is per person)
     */
    public function getOriginalPriceAttribute(): float
    {
        if ($this->is_base) {
            return $this->total_price * $this->pax_count; // always 1 × price
        }

        $base = $this->baseTier();
        if (!$base) {
            return 0;
        }

        return $base->total_price * $this->pax_count;
    }

    /**
     * Discount percentage compared to the original (base) price.
     */
    public function getDiscountPercentageAttribute(): float
    {
        $original = $this->original_price;
        if ($original <= 0) {
            return 0;
        }

        $saving = $original - $this->total_price;
        if ($saving <= 0) {
            return 0;
        }

        return round(($saving / $original) * 100);
    }
}