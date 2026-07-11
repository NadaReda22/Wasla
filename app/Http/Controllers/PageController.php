<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function landing()
    {
        // 1. Create your dummy data here
        $trips = [
            (object)[
                'id' => '1', 
                'title' => 'Sharm to Dahab', 
                'description' => 'Comfortable trip from Sharm El Sheikh to Dahab.', 
                'bus_type' => 'VIP AC Bus', 
                'origin' => 'Sharm', 
                'destination' => 'Dahab', 
                'duration_minutes' => 60, 
                'capacity' => 30, 
                'flat_price' => 4, 
                'image_url' => 'https://images.pexels.com/photos/3601425/pexels-photo-3601425.jpeg',
                'departure_times' => ['08:00 AM', '12:00 PM', '04:00 PM']
            ],
            (object)[
                'id' => '2', 
                'title' => 'Airport Transfer', 
                'description' => 'Direct transfer from SSH Airport to your hotel.', 
                'bus_type' => 'AC Minibus', 
                'origin' => 'Airport', 
                'destination' => 'Hotels', 
                'duration_minutes' => 30, 
                'capacity' => 14, 
                'flat_price' => 4, 
                'image_url' => 'https://images.pexels.com/photos/3278215/pexels-photo-3278215.jpeg',
                'departure_times' => ['On Demand']
            ],
            (object)[
                'id' => '3', 
                'title' => 'Old Market Tour', 
                'description' => 'Evening tour to the Old Market and Sahaba Mosque.', 
                'bus_type' => 'AC Bus', 
                'origin' => 'Hotels', 
                'destination' => 'Old Market', 
                'duration_minutes' => 45, 
                'capacity' => 30, 
                'flat_price' => 4, 
                'image_url' => 'https://images.pexels.com/photos/460672/pexels-photo-460672.jpeg',
                'departure_times' => ['07:00 PM', '09:00 PM']
            ]
        ];

        // 2. Pass the data to the view
        return view('pages.landing', compact('trips'));
    }
}