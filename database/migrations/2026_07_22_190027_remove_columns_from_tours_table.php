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
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('from_location');
            $table->dropColumn('duration');
            $table->dropColumn('base_seats');      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->string('from_location')->after('id');
            $table->integer('duration')->after('from_location');
            $table->integer('base_seats')->after('duration');
        });
    }
};
