<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\GreenApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $phone;
    public $message;

    // Allow the job to be retried up to 3 times if it fails
    public $tries = 3;
    
    // How many seconds to wait before trying again
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bookingId, string $phone, string $message)
    {
        $this->bookingId = $bookingId;
        $this->phone = $phone;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(GreenApiService $greenApi): void
    {
        // ANTI-BAN PROTECTION: Random delay between 3 and 8 seconds
        // This prevents WhatsApp from flagging the number for sending bulk automated spam
        sleep(rand(3, 8));

        // Mark as pending before we attempt
        Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'pending']);

        $result = $greenApi->sendMessage($this->phone, $this->message);

        if (!$result['success']) {
            Log::error("Failed to send queued WhatsApp message to {$this->phone}", [
                'booking_id' => $this->bookingId,
                'error' => $result['error']
            ]);
            
            // Throwing an exception triggers Laravel's built-in retry mechanism
            throw new \Exception("WhatsApp API Error: " . json_encode($result['error']));
        }

        // Success! Update booking status
        Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'sent']);
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        Log::critical("WhatsApp message permanently failed for booking ID {$this->bookingId}.");
        
        Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'failed']);
    }
}