<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreenApiService
{
    private string $host;
    private ?string $instanceId;
    private ?string $token;

    public function __construct()
    {
        $this->host = rtrim(config('services.green_api.host'), '/');
        $this->instanceId = config('services.green_api.instance_id');
        $this->token = config('services.green_api.token');
    }

    public function isConfigured(): bool
    {
        return !empty($this->instanceId) && !empty($this->token);
    }

    /**
     * Returns one of: 'authorized', 'notAuthorized', 'blocked', 'starting',
     * 'sleepMode', 'yellowCard', 'error', or null if the call itself failed.
     */
    public function getInstanceState(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get(
                "{$this->host}/waInstance{$this->instanceId}/getStateInstance/{$this->token}"
            );

            if ($response->successful()) {
                return $response->json('stateInstance');
            }

            Log::warning('Green API state check failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Green API connection failed during state check', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function isAuthorized(): bool
    {
        return $this->getInstanceState() === 'authorized';
    }

    public function sendMessage(string $chatId, string $message): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'not_configured'];
        }

        try {
            $response = Http::timeout(10)->post(
                "{$this->host}/waInstance{$this->instanceId}/sendMessage/{$this->token}",
                ['chatId' => $chatId, 'message' => $message]
            );

            if ($response->successful()) {
                return ['success' => true, 'idMessage' => $response->json('idMessage')];
            }

            return ['success' => false, 'error' => 'http_' . $response->status(), 'body' => $response->body()];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return ['success' => false, 'error' => 'connection_failed'];
        }
    }
}