<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDeparture extends Model
{
    protected $fillable = ['tour_id', 'departure_time', 'departure_date', 'is_active'];
    protected $casts = ['departure_time' => 'datetime:H:i', 'departure_date' => 'date', 'is_active' => 'boolean'];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
