<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
 protected $fillable = ['booking_id', 'kashier_order_id', 'amount', 'currency', 'status'];

public function booking()
{
    return $this->belongsTo(Booking::class);
}
}
