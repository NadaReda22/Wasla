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
        Schema::table('settings', function (Blueprint $table) {
           $table->string('landing_background')->nullable()->after('brand_logo');
           $table->json('fleet_images')->nullable()->after('landing_background');
           $table->string('booking_background')->nullable()->after('landing_background');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
          $table->dropColumn('landing_background');     
          $table->dropColumn('fleet_images');
            $table->dropColumn('booking_background');
        });
    }
};
