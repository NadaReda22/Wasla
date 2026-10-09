<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {

        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['parent_booking_id']);
            });
        } catch (\Exception $e) {
            // Foreign key is already gone, continue safely...
        }

        // 2. Update existing NULL data safely
        DB::table('bookings')
            ->whereNull('room_number')
            ->update(['room_number' => 'N/A']);
            
        // 3. Perform the rest of the column changes with safety checks
        Schema::table('bookings', function (Blueprint $table) {
            
            if (Schema::hasColumn('bookings', 'parent_booking_id')) {
                $table->dropColumn('parent_booking_id');
            }
            
            if (Schema::hasColumn('bookings', 'kashier_order_id')) {
                $table->dropColumn('kashier_order_id');
            }
            
            if (Schema::hasColumn('bookings', 'special_requests')) {
                $table->dropColumn('special_requests');
            }
            
            // Add the reception toggle (only if it doesn't already exist from a previous attempt)
            if (!Schema::hasColumn('bookings', 'meet_at_reception')) {
                $table->boolean('meet_at_reception')->default(false)->after('room_number');
            }
            
            // Make room_number required
            $table->string('room_number')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'kashier_order_id')) {
                $table->string('kashier_order_id')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'special_requests')) {
                $table->text('special_requests')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'parent_booking_id')) {
                $table->unsignedBigInteger('parent_booking_id')->nullable();
            }
            if (Schema::hasColumn('bookings', 'meet_at_reception')) {
                $table->dropColumn('meet_at_reception');
            }
            
            $table->string('room_number')->nullable()->change();
        });
    }
};