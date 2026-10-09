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
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');

            $table->dropColumn('payment_status');

            $table->renameColumn('pickup_latitude', 'pickup_lat');
            $table->renameColumn('pickup_longitude', 'pickup_lng');

            $table->string('pickup_name')->nullable()->after('seats_count');
            // $table->json('extra_whatsapp_numbers')->nullable()->after('special_requests');
            $table->boolean('is_cash_on_board_paid')->default(false)->after('cash_due_on_board');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('hotel_id')->constrained();
            $table->enum('payment_status', [
                'pending', 
                'deposit_paid', 
                'cash_on_board', 
                'completed', 
                'cancelled'
            ]);
            $table->renameColumn('pickup_lat', 'pickup_latitude');
            $table->renameColumn('pickup_lng', 'pickup_longitude');
            $table->dropColumn(['pickup_name', 'is_cash_on_board_paid']);
        });
    }
};
