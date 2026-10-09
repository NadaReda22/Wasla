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
        if (!Schema::hasColumn('bookings', 'reception_number')) {
            $table->string('reception_number', 50)->nullable()->after('room_number');
        }
        if (Schema::hasColumn('bookings', 'meet_at_reception')) {
            $table->dropColumn('meet_at_reception');
        }
    });
}

public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        if (Schema::hasColumn('bookings', 'reception_number')) {
            $table->dropColumn('reception_number');
        }
        if (!Schema::hasColumn('bookings', 'meet_at_reception')) {
            $table->boolean('meet_at_reception')->default(false)->after('room_number');
        }
    });
}
};
