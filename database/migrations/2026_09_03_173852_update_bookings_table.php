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
       Schema::table('bookings', function (Blueprint $table) {
      
            
            // Logistics & Capacity
            $table->integer('seats_count')->default(1)->after('vehicle_id');

            // Google Maps Tracking Data
            $table->decimal('pickup_latitude', 10, 8)->nullable()->after('hotel_id');
            $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
            $table->string('pickup_address')->nullable()->after('pickup_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
      
        $table->dropColumn(['seats_count', 'pickup_latitude', 'pickup_longitude', 'pickup_address']);
    });
    }
};
