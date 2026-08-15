<?php

// app/Models/Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'brand_name',
        'brand_slogan',
        'brand_logo',
        'admin_name',
        'admin_image',
        'minimum_tour_cost_dollar',
        'deposit_amount_dollar',
        'offer_bar_text',
        'announcement_card_text',
        'contact_email',
        'contact_phone',
        'hide_coverage_area',
    ];

    protected $casts = [
        'minimum_tour_cost_dollar' => 'decimal:2',
        'deposit_amount_dollar' => 'decimal:2',
    ];

    // Helper to get the only settings row
    public static function current(): ?self
    {
        return static::first();
    }
}