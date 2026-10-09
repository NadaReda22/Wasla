<?php

// Declare the namespace for Laravel console commands
namespace App\Console\Commands;

// Import the base Command class provided by Laravel
use Illuminate\Console\Command;
// Import the Http facade to make API requests to OpenStreetMap
use Illuminate\Support\Facades\Http;
// Import the Hotel model to interact with the hotels database table
use App\Models\Hotel;

// Define the class FetchHotelsFromOSM which extends the base Command class
class FetchHotelsFromOSM extends Command
{
    /**
     * The name and signature of the console command.
     * This is what you type in the terminal to run it.
     *
     * @var string
     */
    // Change the signature to a cleaner, easy-to-remember command name
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
    // Define the main handle method
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
                ->timeout(120) // Give the server time to process
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
                // Exit the command early so we do not process empty data
                return;
            }

            // Parse the JSON response body into a PHP associative array
            $data = $response->json();
            
            // Extract the 'elements' array from the response data, default to an empty array if missing
            $elements = $data['elements'] ?? [];
            
            // Initialize a counter to keep track of how many hotels are successfully saved
            $count = 0;

            // Check if the elements array is completely empty
            if (empty($elements)) {
                // Show a warning message in the terminal if no data is found
                $this->warn('No hotels found. Please check the API query.');
                // Exit the command early
                return;
            }

            // Start a visual progress bar in the terminal based on the total number of elements fetched
            $this->output->progressStart(count($elements));

            // Loop through each element (hotel) returned by the API
            foreach ($elements as $element) {
                // Extract the tags array which contains hotel details (names, etc.), default to empty array
                $tags = $element['tags'] ?? [];

                // Try to get the English name, fallback to int_name, then fallback to general name, else null
                $nameEn = $tags['name:en'] ?? $tags['int_name'] ?? $tags['name'] ?? null;
                
                // Try to get the Arabic name, fallback to general name, else null
                $nameAr = $tags['name:ar'] ?? $tags['name'] ?? null;

                // If we could not find an English name, we cannot save it properly
                if (!$nameEn) {
                    // Advance the progress bar even if we skip the record
                    $this->output->progressAdvance();
                    // Skip to the next element in the loop
                    continue;
                }

                // Extract latitude: if it's a way/relation use center.lat, if it's a node use lat
                $lat = $element['center']['lat'] ?? $element['lat'] ?? null;
                
                // Extract longitude: if it's a way/relation use center.lon, if it's a node use lon
                $lng = $element['center']['lon'] ?? $element['lon'] ?? null;

                // If either latitude or longitude is missing, we cannot place the pin on the map
                if (!$lat || !$lng) {
                    // Advance the progress bar even if we skip
                    $this->output->progressAdvance();
                    // Skip to the next element in the loop
                    continue;
                }

                // Insert the hotel into the database, or update it if it already exists
                // updateOrCreate prevents duplicate entries when you run the command multiple times
                Hotel::updateOrCreate(
                    // Condition: search for an existing hotel with this exact English name
                    ['name_en' => $nameEn], 
                    // The values to insert or update
                    [
                        // Save the Arabic name
                        'name_ar' => $nameAr,
                        // Save the latitude coordinate
                        'latitude' => $lat,
                        // Save the longitude coordinate
                        'longitude' => $lng,
                        // Set the hotel as active by default
                        'is_active' => true,
                        // Set the default extra transfer fee to 0.00
                        'extra_transfer_fee' => 0.00,
                        // Note: area_id is intentionally left as null so the admin can assign it later
                    ]
                );

                // Increment the counter for successfully saved hotels
                $count++;
                
                // Advance the progress bar for this successful operation
                $this->output->progressAdvance();
            }

            // Finish and close the progress bar in the terminal UI
            $this->output->progressFinish();
            
            // Add an empty line in the terminal for better formatting and spacing
            $this->newLine();
            
            // Display a success message showing the total count of unique hotels saved
            $this->info("Successfully fetched and saved {$count} hotels to your database!");

        } catch (\Exception $e) {
            // If any PHP exception occurs (e.g., database connection error), catch it here
            // Display the exact error message in the terminal for debugging
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}