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

    // sleep(3-8s) + HTTP timeout (10s) fits comfortably; keep config/queue.php retry_after higher than this
    public $timeout = 60;

    public function __construct(int $bookingId, string $phone, string $message)
    {
        $this->bookingId = $bookingId;
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(GreenApiService $greenApi): void
    {

        // Integration disabled or credentials removed after the job was queued: retrying is pointless.
        if (!$greenApi->isEnabled() || !$greenApi->isConfigured()) {
            Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'failed']);
            $this->fail(new \Exception('WhatsApp integration disabled or not configured'));
            return;
        }

        // ANTI-BAN PROTECTION: random delay between 3 and 8 seconds
        sleep(rand(3, 8));

        Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'pending']);

        try {
            $result = $greenApi->sendMessage($this->phone, $this->message);

            if (!$result['success']) {
                Log::error("Failed to send queued WhatsApp message to {$this->phone}", [
                    'booking_id' => $this->bookingId,
                    'error'      => $result['error'] ?? 'Unknown API Error',
                    'body'       => $result['body'] ?? null,
                ]);

                // Throwing an exception triggers Laravel's built-in retry mechanism
                throw new \Exception('WhatsApp API Error: ' . json_encode($result['error'] ?? 'Unknown Error'));
            }

            // "sent" = accepted by Green API (not a delivery receipt)
            Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'sent']);

        } catch (Throwable $e) {
            Log::error("GreenAPI Request Exception for booking ID {$this->bookingId}: " . $e->getMessage());
            throw $e; 
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::critical("WhatsApp message permanently failed for booking ID {$this->bookingId}.", [
            'reason' => $exception->getMessage(),
        ]);

        Booking::where('id', $this->bookingId)->update(['whatsapp_status' => 'failed']);
    }
}