<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\TourPricingTier;
use App\Models\Client;
use App\Models\Booking;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Disable foreign keys and clear previous test records
        Schema::disableForeignKeyConstraints();
        Booking::truncate();
        Client::truncate();
        TourDeparture::truncate();
        TourPricingTier::truncate();
        Tour::truncate();
        Vehicle::truncate();
        Area::truncate();
        Schema::enableForeignKeyConstraints();

        $today = Carbon::today()->toDateString();

        // =========================================================================
        // 1. SEED SHARM EL SHEIKH ZONES (AREAS)
        // =========================================================================
        $nabq = Area::create([
            'name_en' => 'Nabq Bay',
            'name_ar' => 'خليج نبق',
            'latitude' => 28.0382,
            'longitude' => 34.4338,
            'is_in_free_zone' => true,
            'is_active' => true,
        ]);

        $naama = Area::create([
            'name_en' => 'Naama Bay',
            'name_ar' => 'خليج نعمة',
            'latitude' => 27.9158,
            'longitude' => 34.3299,
            'is_in_free_zone' => true,
            'is_active' => true,
        ]);

        $hadaba = Area::create([
            'name_en' => 'Hadaba',
            'name_ar' => 'الهضبة',
            'latitude' => 27.8631,
            'longitude' => 34.3167,
            'is_in_free_zone' => true,
            'is_active' => true,
        ]);

        // =========================================================================
        // 2. SEED VEHICLES (FLEET)
        // =========================================================================
        $busA = Vehicle::create([
            'plate_number' => 'SHM-101',
            'type' => 'microbus',
            'capacity' => 14,
            'driver_name' => 'Ahmed Mahmoud',
            'driver_phone' => '+20 100 111 2222',
            'is_active' => true,
        ]);

        $busB = Vehicle::create([
            'plate_number' => 'SHM-202',
            'type' => 'microbus',
            'capacity' => 14,
            'driver_name' => 'Mohamed Youssef',
            'driver_phone' => '+20 100 333 4444',
            'is_active' => true,
        ]);

        $car1 = Vehicle::create([
            'plate_number' => 'SHM-303',
            'type' => 'private_car',
            'capacity' => 4,
            'driver_name' => 'Tarek Zaki',
            'driver_phone' => '+20 100 555 6666',
            'is_active' => true,
        ]);

        // =========================================================================
        // 3. SEED TOURS
        // =========================================================================
        // Tour 1: Drop & Go (Bus does not wait all day)
        $tourRasMohamed = Tour::create([
            'name_en' => 'Ras Mohammed Snorkeling Boat',
            'name_ar' => 'رحلة يخت رأس محمد والغطس',
            'slug' => 'ras-mohammed-snorkeling',
            'location' => 'Marina Jetty',
            'tag_name' => 'Best Seller',
            'tag_color_class' => 'bg-blue-500 text-white',
            'image_url' => 'tours/ras-mohammed.jpg',
            'description_en' => '<p>Sail to Ras Mohammed national park with open buffet lunch.</p>',
            'description_ar' => '<p>أجمل رحلة بحرية لمحمية رأس محمد مع الغداء والمشروبات.</p>',
            // 'base_seats' => 20,
            'is_bus_staying' => false, // Drop & Go
            'is_active' => true,
        ]);

        // Tour 2: Full Day Stay (Bus stays on-site with guests)
        $tourSafari = Tour::create([
            'name_en' => 'Desert Quad Safari & Bedouin Dinner',
            'name_ar' => 'سفاري الموتوسيكلات والعشاء البدوي',
            'slug' => 'desert-quad-safari',
            'location' => 'Wadi Mander',
            'tag_name' => 'Adventure',
            'tag_color_class' => 'bg-yellow-500 text-black',
            'image_url' => 'tours/safari.jpg',
            'description_en' => '<p>Drive quads into the Sinai mountains followed by a Bedouin show.</p>',
            'description_ar' => '<p>سفاري بيتش باجي في صحراء سيناء مع حفلة وعشاء بدوي.</p>',
            // 'base_seats' => 14,
            'is_bus_staying' => true, // Stays with group
            'is_active' => true,
        ]);

        // =========================================================================
        // 4. SEED DEPARTURES (Schedule)
        // =========================================================================
        // Ras Mohammed: Daily 08:30 Outbound / 16:30 Return
        $depRasMohamed = TourDeparture::create([
            'tour_id' => $tourRasMohamed->id,
            'departure_date' => null, // Daily
            'departure_time' => '08:30:00',
            'return_time' => '16:30:00',
            'is_active' => true,
        ]);

        // Safari: Today specific 15:00 Outbound / 20:30 Return
        $depSafari = TourDeparture::create([
            'tour_id' => $tourSafari->id,
            'departure_date' => $today,
            'departure_time' => '15:00:00',
            'return_time' => '20:30:00',
            'is_active' => true,
        ]);

        // =========================================================================
        // 5. SEED PRICING TIERS
        // =========================================================================
        // Microbus Base & Offer for Ras Mohammed
        $tierRasBase = TourPricingTier::create([
            'tour_id' => $tourRasMohamed->id,
            'offer_name' => 'Standard One Way',
            'is_base' => true,
            'trip_type' => 'one_way',
            'vehicle_type' => 'microbus',
            'pax_count' => 1,
            'total_price' => 20.00,
            'deposit_amount' => 5.00,
            'is_active' => true,
        ]);

        $tierRasRound = TourPricingTier::create([
            'tour_id' => $tourRasMohamed->id,
            'offer_name' => 'Standard Round Trip',
            'is_base' => true,
            'trip_type' => 'round_trip',
            'vehicle_type' => 'microbus',
            'pax_count' => 1,
            'total_price' => 35.00,
            'deposit_amount' => 10.00,
            'is_active' => true,
        ]);

        $tierSafariRound = TourPricingTier::create([
            'tour_id' => $tourSafari->id,
            'offer_name' => 'Safari Standard Round Trip',
            'is_base' => true,
            'trip_type' => 'round_trip',
            'vehicle_type' => 'microbus',
            'pax_count' => 1,
            'total_price' => 45.00,
            'deposit_amount' => 15.00,
            'is_active' => true,
        ]);

        // =========================================================================
        // 6. SEED CLIENTS
        // =========================================================================
        $client1 = Client::create([
            'full_name' => 'John Miller',
            'whatsapp_number' => '+447911123456',
            'nationality' => 'GB',
        ]);

        $client2 = Client::create([
            'full_name' => 'Olga Smirnova',
            'whatsapp_number' => '+79161234567',
            'nationality' => 'RU',
        ]);

        $client3 = Client::create([
            'full_name' => 'Karim Mansour',
            'whatsapp_number' => '+201019876543',
            'nationality' => 'EG',
        ]);

        // =========================================================================
        // 7. SEED BOOKINGS (For Testing Fleet Distribution Dashboard)
        // =========================================================================

        // Booking A: Unassigned (Nabq zone, Ras Mohammed, 4 seats, Round Trip)
        Booking::create([
            'booking_reference' => 'BKG-NABQ01',
            'client_id' => $client1->id,
            'tour_id' => $tourRasMohamed->id,
            'tour_departure_id' => $depRasMohamed->id,
            'tour_pricing_tier_id' => $tierRasRound->id,
            'area_id' => $nabq->id,
            'trip_type' => 'round_trip',
            'booking_date' => $today,
            'room_number' => '102',
            'seats_count' => 4,
            'total_price' => 140.00,
            'deposit_paid' => 40.00,
            'cash_due_on_board' => 100.00,
            'pickup_name' => 'Rixos Seagate Nabq',
            'pickup_lat' => 28.0390,
            'pickup_lng' => 34.4345,
            'pickup_address' => 'Nabq Bay, Sharm El Sheikh',
            'outbound_vehicle_id' => null, // Unassigned for testing
            'return_vehicle_id' => null,
            'is_cash_on_board_paid' => false,
        ]);

        // Booking B: Unassigned (Naama Bay zone, Ras Mohammed, 2 seats, Round Trip)
        Booking::create([
            'booking_reference' => 'BKG-NAAMA02',
            'client_id' => $client2->id,
            'tour_id' => $tourRasMohamed->id,
            'tour_departure_id' => $depRasMohamed->id,
            'tour_pricing_tier_id' => $tierRasRound->id,
            'area_id' => $naama->id,
            'trip_type' => 'round_trip',
            'booking_date' => $today,
            'room_number' => '405',
            'seats_count' => 2,
            'total_price' => 70.00,
            'deposit_paid' => 20.00,
            'cash_due_on_board' => 50.00,
            'pickup_name' => 'Maritim Jolie Ville Resort',
            'pickup_lat' => 27.9160,
            'pickup_lng' => 34.3310,
            'pickup_address' => 'Naama Bay Strip',
            'outbound_vehicle_id' => null, // Unassigned for testing
            'return_vehicle_id' => null,
            'is_cash_on_board_paid' => false,
        ]);

        // Booking C: Pre-assigned to Bus A (Hadaba zone, 6 seats)
        // This tests that Bus A displays 6/14 seats occupied and shows the 'Hadaba' zone badge!
        Booking::create([
            'booking_reference' => 'BKG-HADABA03',
            'client_id' => $client3->id,
            'tour_id' => $tourRasMohamed->id,
            'tour_departure_id' => $depRasMohamed->id,
            'tour_pricing_tier_id' => $tierRasRound->id,
            'area_id' => $hadaba->id,
            'trip_type' => 'round_trip',
            'booking_date' => $today,
            'room_number' => '210',
            'seats_count' => 6,
            'total_price' => 210.00,
            'deposit_paid' => 60.00,
            'cash_due_on_board' => 150.00,
            'pickup_name' => 'Jaz Fanara Resort',
            'pickup_lat' => 27.8635,
            'pickup_lng' => 34.3170,
            'pickup_address' => 'Ras Om El Sid Cliff, Hadaba',
            'outbound_vehicle_id' => $busA->id, // Pre-assigned
            'return_vehicle_id' => $busA->id,
            'is_cash_on_board_paid' => false,
        ]);
    
    // =========================================================================
        // 4. SEED DEPARTURES (Schedule)
        // =========================================================================
        // Ras Mohammed: Outbound 08:30 / Return 16:30 for Today
        $depRasMohamed = TourDeparture::create([
            'tour_id' => $tourRasMohamed->id,
            'departure_date' => $today, // Changed from null to $today
            'departure_time' => '08:30:00',
            'return_time' => '16:30:00',
            'is_active' => true,
        ]);

        // Safari: Outbound 15:00 / Return 20:30 for Today
        $depSafari = TourDeparture::create([
            'tour_id' => $tourSafari->id,
            'departure_date' => $today,
            'departure_time' => '15:00:00',
            'return_time' => '20:30:00',
            'is_active' => true,
        ]);
    }
}