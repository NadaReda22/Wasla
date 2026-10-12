<?php

namespace App\Services;

use App\Support\Integrations;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreenApiService
{
    private string $host;
    private ?string $instanceId;
    private ?string $token;
    private bool $enabled;

    public function __construct()
    {
        $cfg = Integrations::whatsapp();

        $this->host       = rtrim((string) $cfg['host'], '/');
        $this->instanceId = $cfg['instance_id'];
        $this->token      = $cfg['token'];
        $this->enabled    = (bool) $cfg['enabled'];
    }

    /** Admin toggle from Settings → Integrations. */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isConfigured(): bool
    {
        return !empty($this->host) && !empty($this->instanceId) && !empty($this->token);
    }

  
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
        } catch (ConnectionException $e) {
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
        if (!$this->enabled || !$this->isConfigured()) {
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
        } catch (ConnectionException $e) {
            return ['success' => false, 'error' => 'connection_failed'];
        }
    }
}