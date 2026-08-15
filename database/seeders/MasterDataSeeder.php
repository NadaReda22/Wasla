<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Tour;
use App\Models\Faq;
use App\Models\TourDeparture;
use App\Models\TourPricingTier;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEED FAQS
        $faqs = [
            ['q' => 'How much does a ride cost?', 'a' => 'Prices vary by vehicle. Microbus starts at $4, and Private Cars are available for families.'],
            ['q' => 'How do I pay the deposit?', 'a' => 'You can pay the $1 deposit online using our secure payment gateway (Visa, Mastercard, or Fawry).'],
            ['q' => 'How early should I book?', 'a' => 'We recommend booking at least 24 hours in advance.'],
            ['q' => 'Can I cancel my booking?', 'a' => 'Yes, you can cancel for free up to 12 hours before your trip.'],
            ['q' => 'What if the bus is late?', 'a' => 'Our buses are very punctual, but if there is a delay, we will send you a WhatsApp notification.'],
        ];

        $sortOrder = 1;
        foreach ($faqs as $faq) {
            Faq::create([
                'question_en' => $faq['q'],
                'answer_en' => $faq['a'],
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ]);
        }

        // 2. SEED AREAS
        $destinations = [
            ['name' => 'Naama Bay', 'arabicName' => 'نعمة باي', 'lat' => 27.9158, 'lng' => 34.3299],
            ['name' => 'Ras Mohamed', 'arabicName' => 'رأس محمد', 'lat' => 27.7267, 'lng' => 34.2443],
            ['name' => 'Airport', 'arabicName' => 'المطار', 'lat' => 27.9774, 'lng' => 34.3949],
            ['name' => 'Old Market', 'arabicName' => 'السوق القديم', 'lat' => 27.8642, 'lng' => 34.2952],
            ['name' => 'Dahab', 'arabicName' => 'دهب', 'lat' => 28.5010, 'lng' => 34.5134],
        ];

        foreach ($destinations as $dest) {
            Area::create([
                'name_en' => $dest['name'],
                'name_ar' => $dest['arabicName'],
                'latitude' => $dest['lat'],
                'longitude' => $dest['lng'],
                'is_in_free_zone' => true,
            ]);
        }

        // 3. SEED TOURS, DEPARTURES, AND PRICING TIERS
        $baseTours = [
            [
                'name' => 'Naama Bay Express', 'arabicName' => 'نعمة باي', 'from' => 'City Center / Hotels', 'duration' => '15 min', 'tag' => 'Most Popular', 'tagClass' => 'bg-primary text-white', 'departures' => ['07:00','09:00','11:00','15:00'], 'baseSeats' => 14, 'description' => 'Direct transfer to the heart of Naama Bay.'
            ],
            [
                'name' => 'Ras Mohamed National Park', 'arabicName' => 'رأس محمد', 'from' => 'Naama Bay', 'duration' => '45 min', 'tag' => '🐠 Snorkeling', 'tagClass' => 'bg-turquoise text-white', 'departures' => ['07:00','09:00'], 'baseSeats' => 14, 'description' => "Visit Egypt's most famous marine park."
            ]
        ];

        foreach ($baseTours as $tourData) {
            $tour = Tour::create([
                'name_en' => $tourData['name'],
                'name_ar' => $tourData['arabicName'],
                'from_location' => $tourData['from'],
                'duration' => $tourData['duration'],
                'tag_name' => $tourData['tag'],
                'tag_color_class' => $tourData['tagClass'],
                'base_seats' => $tourData['baseSeats'],
                'description_en' => $tourData['description'],
                'image_url' => '/images/default-tour.jpg',
                'is_active' => true,
            ]);

            // Add Departures
            foreach ($tourData['departures'] as $time) {
                TourDeparture::create([
                    'tour_id' => $tour->id,
                    'departure_time' => $time,
                ]);
            }

            // --- THE PRICING LOGIC YOU REQUESTED ---
            
            // 1. Microbus - One Way (1 Person)
            TourPricingTier::create([
                'tour_id' => $tour->id,
                'offer_name' => 'Microbus (1-Way)',
                'trip_type' => 'one_way',
                'pax_count' => 1,
                'base_price_one_way' => 4.00,
                'total_price' => 4.00,
                'deposit_amount' => 1.00,
            ]);

            // 2. Microbus - Round Trip (1 Person - Save 25%)
            TourPricingTier::create([
                'tour_id' => $tour->id,
                'offer_name' => 'Microbus (Round Trip)',
                'trip_type' => 'round_trip',
                'pax_count' => 1,
                'base_price_one_way' => 4.00, 
                'total_price' => 6.00, // Discounted from $8
                'deposit_amount' => 2.00,
            ]);

            // 3. Microbus - Couple's Round Trip (2 People)
            TourPricingTier::create([
                'tour_id' => $tour->id,
                'offer_name' => 'Couple Special (Round Trip)',
                'trip_type' => 'round_trip',
                'pax_count' => 2,
                'base_price_one_way' => 4.00, 
                'total_price' => 10.00, // Extra discount for couples
                'deposit_amount' => 2.00,
            ]);

            // 4. Private Car - One Way (Up to 4 People)
            TourPricingTier::create([
                'tour_id' => $tour->id,
                'offer_name' => 'Private Car (1-Way)',
                'trip_type' => 'one_way',
                'pax_count' => 4, // Car covers up to 4 people
                'base_price_one_way' => 15.00,
                'total_price' => 15.00,
                'deposit_amount' => 3.00,
            ]);
        }
    }
}