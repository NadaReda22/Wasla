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
        // 1. Update the 'areas' table
        Schema::table('areas', function (Blueprint $table) {
            // Drop the polygon column since we are not using geofencing anymore
            if (Schema::hasColumn('areas', 'polygon_coordinates')) {
                $table->dropColumn('polygon_coordinates');
            }
        });

        // 2. Update the 'hotels' table
        Schema::table('hotels', function (Blueprint $table) {
            // Make area_id nullable so we can fetch hotels first and assign them later
            $table->unsignedBigInteger('area_id')->nullable()->change();
            
            if (!Schema::hasColumn('hotels', 'extra_transfer_fee')) {
                $table->decimal('extra_transfer_fee', 8, 2)->default(0.00)->after('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->text('polygon_coordinates')->nullable();
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->unsignedBigInteger('area_id')->nullable(false)->change();
            $table->dropColumn('extra_transfer_fee');
        });
    }
};