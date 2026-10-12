<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Hotel;

class FetchHotelsFromOSM extends Command
{
    /**
     * The name and signature of the console command.
     * This is what you type in the terminal to run it.
     *
     * @var string
     */
    protected $signature = 'hotels:fetch-osm';

    /**
     * The console command description.
     * This shows up when you run `php artisan list` in the terminal.
     *
     * @var string
     */
    // Provide a clear description of what this command does
    protected $description = 'Fetch hotels in Sharm El-Sheikh from OpenStreetMap and save them to the database';

    /**
     * Execute the console command.
     * This method contains the main logic executed when the command runs.
     */
    public function handle()
    {
        // Print an info message to the terminal to let the user know the process started
        $this->info('Fetching hotels from OpenStreetMap... This might take a minute.');

        // Define the Overpass API query using Overpass QL
        // [out:json] tells the API to return the response in JSON format
        // [timeout:60] gives the server 60 seconds to process the query before timing out
        // area["name:en"="Sharm el-Sheikh"]->.searchArea; defines the geographical search boundary
        // node, way, and relation lines search for elements tagged with tourism=hotel inside that boundary
        // out center; ensures we get the exact latitude and longitude center points
        // Optimized Overpass QL query
        // Increased timeout to 120 seconds
        // Used 'nwr' (Node, Way, Relation) shortcut for faster execution
        $query = '
            [out:json][timeout:120];
            (
              nwr["tourism"="hotel"](27.75, 34.20, 28.15, 34.55);
              nwr["leisure"="resort"](27.75, 34.20, 28.15, 34.55);
            );
            out center;
        ';

        try {
            // Send request to Overpass API
            // Added ->timeout(120) to tell Laravel to wait up to 2 minutes for the response
            $response = Http::withoutVerifying()
                ->timeout(120) 
                ->withHeaders([
                    'User-Agent' => 'WaslaApp/1.0 (Local Development)'
                ])
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $query
                ]);

            // Check if the HTTP request failed (e.g., 404, 500 server errors or rate limits)
            if ($response->failed()) {
                // Display the exact error returned by the server for easier debugging
                $this->error('Failed to fetch data from Overpass API.');
                $this->error('Status Code: ' . $response->status());
                $this->error('Error Details: ' . $response->body());
                return;
            }

            // Parse the JSON response body into a PHP associative array
            $data = $response->json();
            
            $elements = $data['elements'] ?? [];
            
            $count = 0;

            // Check if the elements array is completely empty
            if (empty($elements)) {
                // Show a warning message in the terminal if no data is found
                $this->warn('No hotels found. Please check the API query.');
                // Exit the command early
                return;
            }

            $this->output->progressStart(count($elements));

            foreach ($elements as $element) {
                $tags = $element['tags'] ?? [];

                $nameEn = $tags['name:en'] ?? $tags['int_name'] ?? $tags['name'] ?? null;
                
                $nameAr = $tags['name:ar'] ?? $tags['name'] ?? null;

                if (!$nameEn) {
                    $this->output->progressAdvance();
                    continue;
                }

                $lat = $element['center']['lat'] ?? $element['lat'] ?? null;
                
                $lng = $element['center']['lon'] ?? $element['lon'] ?? null;

                if (!$lat || !$lng) {
                    $this->output->progressAdvance();
                    continue;
                }

                // Insert the hotel into the database, or update it if it already exists
                // updateOrCreate prevents duplicate entries when you run the command multiple times
                Hotel::updateOrCreate(
                    ['name_en' => $nameEn], 
                    [
                        'name_ar' => $nameAr,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'is_active' => true,
                        'extra_transfer_fee' => 0.00,
                    ]
                );

                $count++;
                
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            
            $this->newLine();
            
            $this->info("Successfully fetched and saved {$count} hotels to your database!");

        } catch (\Exception $e) {

            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}