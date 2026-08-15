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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
           
        
            // Self-referencing for Round Trips (The return trip links to the outbound trip)
            $table->foreignId('parent_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('booking_reference')->index(); 
            
            // Relational Links
            $table->foreignId('tour_id')->constrained();  $table->foreignId('client_id')->constrained();
          
            $table->foreignId('tour_departure_id')->constrained();
            $table->foreignId('tour_pricing_tier_id')->constrained();
            
            // Logistics (Linked to the specific whitelisted hotel)
            $table->foreignId('hotel_id')->constrained();
            $table->string('room_number')->nullable(); // Driver needs this to call the room
            $table->foreignId('vehicle_id')->nullable()->constrained(); // Assigned by admin later
            
            $table->enum('direction', ['outbound', 'return']);
            $table->date('booking_date');
            $table->text('special_requests')->nullable();
            
            // Financial Snapshot (Frozen at the time of booking)
            $table->decimal('total_price', 8, 2)->default(0);
            $table->decimal('deposit_paid', 8, 2)->default(0);
            $table->decimal('cash_due_on_board', 8, 2)->default(0);
            
            $table->enum('payment_status', [
                'pending', 
                'deposit_paid', 
                'cash_on_board', 
                'completed', 
                'cancelled'
            ]);
            
            $table->timestamps();
     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
