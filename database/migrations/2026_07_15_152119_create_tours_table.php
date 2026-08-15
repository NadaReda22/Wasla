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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique(); 
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('image_url');
            $table->string('from_location');
            $table->string('duration');
            $table->string('tag_name')->nullable();
            $table->string('tag_color_class')->nullable();
            $table->text('description_en');
            $table->text('description_ar')->nullable();
            $table->integer('base_seats')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
