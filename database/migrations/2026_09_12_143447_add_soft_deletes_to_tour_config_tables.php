<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_pricing_tiers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tour_departures', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tour_pricing_tiers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tour_departures', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};