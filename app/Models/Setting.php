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
        'landing_background',
        'booking_background',
        'admin_name',
        'admin_image',
        'minimum_tour_cost_dollar',
        'deposit_amount_dollar',
        'offer_bar_text',
        'announcement_card_text',
        'contact_email',
        'contact_phone',
        'hide_coverage_area',
        'offer_bar_text_ru',
        'announcement_card_text_ru',    
        'fleet_images',
        'booking_steps',
        'booking_dos',
        'booking_donts',
        'important_notes',
        'legal_policies',
        'kashier_mode',
        'kashier_test_merchant_id',
        'kashier_test_api_key',
        'kashier_test_webhook_secret',
        'kashier_live_merchant_id',
        'kashier_live_api_key',  
        'kashier_live_webhook_secret',
        'whatsapp_enabled',
        'green_api_host',
        'green_api_instance_id',
        'green_api_token',

    ];

    protected $casts = [
        'minimum_tour_cost_dollar' => 'decimal:2',
        'deposit_amount_dollar' => 'decimal:2',
        'fleet_images' => 'array',
        'booking_steps' => 'array',
        'booking_dos' => 'array',
        'booking_donts' => 'array',
        'important_notes' => 'array',
        'legal_policies' => 'array',
        'kashier_test_api_key' => 'encrypted',
        'kashier_test_webhook_secret' => 'encrypted',
        'kashier_live_api_key' => 'encrypted',
        'kashier_live_webhook_secret' => 'encrypted',
        'green_api_token' => 'encrypted',
        'whatsapp_enabled' => 'boolean',
    ];

    // Helper to get the only settings row
    public static function current(): ?self
    {
        return static::first();
    }
}