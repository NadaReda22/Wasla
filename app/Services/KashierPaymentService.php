<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Support\Integrations;

class KashierPaymentService
{
    protected $kashier;

    public function __construct()
    {
        // Fetch the dynamic credentials (which handles the live/test logic & DB/.env fallback)
        $this->kashier = Integrations::kashier();
    }

    public function createPaymentLink($booking, $depositAmount, $kashierOrderId)
    {
        // Optional safety check
        if (!$this->kashier['configured']) {
            throw new \Exception('Kashier payment gateway is not configured.');
        }

        // Use the base_url determined by your Integrations class
        $endpoint = "{$this->kashier['base_url']}/v3/payment/sessions";

        $client = $booking->client;

        $response = Http::withHeaders([
                'Authorization' => $this->kashier['webhook_secret'],
                'api-key' => $this->kashier['api_key'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(15)
            ->post($endpoint, [
                'merchantId' => $this->kashier['merchant_id'],
                'merchantOrderId' => (string) $kashierOrderId,
                'amount' => number_format((float) $depositAmount, 2, '.', ''),
                'currency' => config('services.kashier.currency', 'USD'), 
                'customer' => [
                    'reference' => (string) ($client->id ?? $booking->client_id ?? uniqid('CUST_')),
                    'name' => (string) ($client->full_name ?? 'Guest'),
                    'email' => (string) ($client->email ?: 'no-email@example.com'),
                    'phone' => (string) ($client->whatsapp_number ?? ''),
                ],
                'merchantRedirect' => route('payment.callback', ['merchantOrderId' => $kashierOrderId]),
                'order' => (string) $kashierOrderId,
                'serverWebhook' => url('/payment/webhook'),
                'expireAt' => now()->addMinutes(30)->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'maxFailureAttempts' => 3,
            ]);

        if ($response->successful()) {
            $data = $response->json();

            $paymentUrl = $data['sessionUrl']
                ?? $data['paymentUrl']
                ?? $data['response']['paymentUrl']
                ?? $data['url']
                ?? $data['response']['url']
                ?? null;

            if ($paymentUrl) {
                return $paymentUrl;
            }
        }

        throw new \Exception('Failed to generate Kashier payment link: ' . $response->body());
    }
}