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
        Schema::table('tour_departures', function (Blueprint $table) {
           $table->date('departure_date')->after('departure_time')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_departures', function (Blueprint $table) {
         $table->date('departure_date')->after('departure_time')->nullable();
        });
    }
};
