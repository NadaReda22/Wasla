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
            $table->json('booking_steps')->nullable();
        $table->json('booking_dos')->nullable();
        $table->json('booking_donts')->nullable();
        $table->json('important_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
        $table->dropColumn('booking_steps');
        $table->dropColumn('booking_dos');
        $table->dropColumn('booking_donts');        
        $table->dropColumn('important_notes');
        });
    }
};
