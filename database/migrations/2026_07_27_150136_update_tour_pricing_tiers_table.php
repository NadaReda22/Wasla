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
        Schema::table('tour_pricing_tiers', function (Blueprint $table) {
        
        $table->string('vehicle_type')->after('tour_id')->default('microbus');
        
        $table->boolean('is_base')->default(false)->after('pax_count');
        
        $table->string('offer_name')->nullable()->change();
        
        
        $table->dropColumn('base_price_one_way');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_pricing_tiers', function (Blueprint $table) {
        
        $table->dropColumn(['vehicle_type', 'is_base']);
        $table->string('offer_name')->nullable(false)->change();
        $table->decimal('base_price_one_way', 8, 2)->nullable();
        });
    }
};
