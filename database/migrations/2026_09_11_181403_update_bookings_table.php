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
            // Drop redundant/obsolete columns
            if (Schema::hasColumn('bookings', 'vehicle_id')) {
                $table->dropConstrainedForeignId('vehicle_id');
            }
            if (Schema::hasColumn('bookings', 'direction')) {
                $table->dropColumn('direction');
            }

            // Ensure trip type exists (one_way vs round_trip)
            if (!Schema::hasColumn('bookings', 'trip_type')) {
                $table->enum('trip_type', ['one_way', 'round_trip'])->default('one_way')->after('room_number');
            }

            // Area/Zone assignment for location intelligence
            if (!Schema::hasColumn('bookings', 'area_id')) {
                $table->foreignId('area_id')->nullable()->after('tour_pricing_tier_id')->constrained('areas')->nullOnDelete();
            }

            // Independent fleet dispatch columns
            if (!Schema::hasColumn('bookings', 'outbound_vehicle_id')) {
                $table->foreignId('outbound_vehicle_id')->nullable()->after('seats_count')->constrained('vehicles')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'return_vehicle_id')) {
                $table->foreignId('return_vehicle_id')->nullable()->after('outbound_vehicle_id')->constrained('vehicles')->nullOnDelete();
            }//
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop new fields
            if (Schema::hasColumn('bookings', 'outbound_vehicle_id')) {
                $table->dropConstrainedForeignId('outbound_vehicle_id');
            }
            if (Schema::hasColumn('bookings', 'return_vehicle_id')) {
                $table->dropConstrainedForeignId('return_vehicle_id');
            }
            if (Schema::hasColumn('bookings', 'area_id')) {
                $table->dropConstrainedForeignId('area_id');
            }
            if (Schema::hasColumn('bookings', 'trip_type')) {
                $table->dropColumn('trip_type');
            }

            // Restore previous columns if rolled back
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->enum('direction', ['outbound', 'return'])->default('outbound');
        });
    }
};
