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
         Schema::table('areas', function (Blueprint $table) {
            $table->string('name_ru')->nullable()->after('name_ar');
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->string('name_ru')->nullable()->after('name_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('name_ru');
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('name_ru');
        });
    }
};

