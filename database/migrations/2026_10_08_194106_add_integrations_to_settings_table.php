<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Kashier: one credential set per mode, so switching mode never means re-typing keys
            $table->string('kashier_mode')->default('test');

            $table->string('kashier_test_merchant_id')->nullable();
            $table->text('kashier_test_api_key')->nullable();          // encrypted cast (long ciphertext => text)
            $table->text('kashier_test_webhook_secret')->nullable();   // encrypted cast

            $table->string('kashier_live_merchant_id')->nullable();
            $table->text('kashier_live_api_key')->nullable();          // encrypted cast
            $table->text('kashier_live_webhook_secret')->nullable();   // encrypted cast

            // WhatsApp (Green API) has no test/live mode, only on/off
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('green_api_host')->nullable();
            $table->string('green_api_instance_id')->nullable();
            $table->text('green_api_token')->nullable();               // encrypted cast
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'kashier_mode',
                'kashier_test_merchant_id', 'kashier_test_api_key', 'kashier_test_webhook_secret',
                'kashier_live_merchant_id', 'kashier_live_api_key', 'kashier_live_webhook_secret',
                'whatsapp_enabled', 'green_api_host', 'green_api_instance_id', 'green_api_token',
            ]);
        });
    }
};