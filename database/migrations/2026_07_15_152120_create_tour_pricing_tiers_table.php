<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tour_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
        
            $table->string('offer_name'); // e.g., "Triple Round Trip"
            $table->enum('trip_type', ['one_way', 'round_trip']);
            $table->integer('pax_count'); // How many seats this offer covers
            
            $table->decimal('base_price_one_way', 8, 2); // Used to calculate the discount %
            $table->decimal('total_price', 8, 2); // The actual price the client pays
            $table->decimal('deposit_amount', 8, 2);
        
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_pricing_tiers');
    }
};
