<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->string('kashier_order_id')->nullable()->after('booking_reference');
        $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('is_cash_on_board_paid');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('kashier_order_id');
            $table->dropColumn('payment_status');   
        });
    }
};
