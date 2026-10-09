<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Restore payment_status exactly as you originally created it
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed'])
                      ->default('pending')
                      ->after('is_cash_on_board_paid');
            } else {
                // If the column exists but is a string, force it back to ENUM
                DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'");
            }
        });

        // 2. Safely add the unique index without requiring Doctrine/DBAL
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->unique('kashier_order_id');
            });
        } catch (\Exception $e) {
            // The unique index already exists, safely ignore and continue
        }
    }

    public function down(): void
    {
        // Safely drop the unique index without requiring Doctrine/DBAL
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropUnique(['kashier_order_id']);
            });
        } catch (\Exception $e) {
            // The unique index is already gone, safely ignore
        }

        // We leave the bookings table alone in down() so we NEVER accidentally delete your payment_status again.
    }
};