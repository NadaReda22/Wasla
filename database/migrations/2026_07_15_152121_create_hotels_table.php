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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
        
            $table->string('name_en'); // e.g., "Rixos Premium Seagate"
            $table->string('name_ar')->nullable();
            
            // Optional map coordinates for future driver GPS integration
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // THE EXCEPTION RULE: Add a specific fee for this exact hotel, if needed.
            $table->decimal('extra_transfer_fee', 8, 2)->default(0.00);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
