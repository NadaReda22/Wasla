<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('offer_bar_text_ru')->nullable()->after('offer_bar_text');
            $table->text('announcement_card_text_ru')->nullable()->after('announcement_card_text');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['offer_bar_text_ru', 'announcement_card_text_ru']);
        });
    }
};