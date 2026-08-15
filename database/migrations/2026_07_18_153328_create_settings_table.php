<?php

// database/migrations/xxxx_xx_xx_create_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Branding
            $table->string('brand_name')->nullable();
            $table->string('brand_slogan')->nullable();
            $table->string('brand_logo')->nullable();           // path to image

            // Dashboard
            $table->string('admin_name')->nullable();
            $table->string('admin_image')->nullable();

            // Landing Page – Tour Pricing
            $table->decimal('minimum_tour_cost_dollar', 10, 2)->nullable();
            $table->decimal('deposit_amount_dollar', 10, 2)->nullable();   // or percentage, you decide

            // Marketing / Announcements
            $table->text('offer_bar_text')->nullable();          // the top offer bar
            $table->text('announcement_card_text')->nullable();  // main announcement

            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};