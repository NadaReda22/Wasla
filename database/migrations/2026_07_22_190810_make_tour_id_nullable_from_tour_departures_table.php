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
             $table->dropForeign(['tour_id']);

            $table->unsignedBigInteger('tour_id')->nullable()->change();

            $table->foreign('tour_id')
                ->references('id')
                ->on('tours')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_departures', function (Blueprint $table) {
             $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
        });
    }
};
