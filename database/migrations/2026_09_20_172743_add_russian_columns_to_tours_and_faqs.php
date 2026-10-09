<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->string('name_ru')->nullable()->after('name_ar');
            $table->text('description_ru')->nullable()->after('description_ar');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question_ru')->nullable()->after('question_en');
            $table->text('answer_ru')->nullable()->after('answer_en');
        });
    }

    public function down()
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['name_ru', 'description_ru']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question_ru', 'answer_ru']);
        });
    }
};