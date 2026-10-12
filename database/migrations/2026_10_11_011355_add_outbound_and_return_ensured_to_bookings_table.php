<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Each direction is locked on its own when "Ensure & Notify" is pressed.
            $table->boolean('is_outbound_ensured')->default(false)->after('is_distribution_ensured');
            $table->boolean('is_return_ensured')->default(false)->after('is_outbound_ensured');
        });

        // Bookings that were already locked keep their lock on every direction they have.
        DB::table('bookings')
            ->where('is_distribution_ensured', true)
            ->update([
                'is_outbound_ensured' => true,
                'is_return_ensured'   => DB::raw("CASE WHEN trip_type = 'round_trip' THEN 1 ELSE 0 END"),
            ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['is_outbound_ensured', 'is_return_ensured']);
        });
    }
};