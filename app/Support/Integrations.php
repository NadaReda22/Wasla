<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Single place the rest of the app asks "what are the Kashier / WhatsApp credentials?".
 * Reads the encrypted values saved from Filament. In TEST mode it falls back to .env
 * (config/services.php) so local development keeps working without touching the admin.
 *
 * NOTE: no static cache on purpose. Queue workers are long-running processes, and a static
 * cache would keep serving old credentials until the worker is restarted.
 */
class Integrations
{
    private static function settings(): ?Setting
    {
        return Setting::first();
    }

    public static function kashier(): array
    {
        $s = self::settings();
        $mode = $s?->kashier_mode === 'live' ? 'live' : 'test';

        $get = function (string $field) use ($s, $mode) {
            $value = $s?->{"kashier_{$mode}_{$field}"};

            // Never fall back to .env in live mode: a test key against the live URL (or the reverse) is a nasty bug.
            if (blank($value) && $mode === 'test') {
                $value = config("services.kashier.{$field}");
            }

            return $value;
        };

        $data = [
            'mode'           => $mode,
            'base_url'       => $mode === 'live' ? 'https://api.kashier.io' : 'https://test-api.kashier.io',
            'merchant_id'    => $get('merchant_id'),
            'api_key'        => $get('api_key'),
            'webhook_secret' => $get('webhook_secret'),
        ];

        $data['configured'] = filled($data['merchant_id']) && filled($data['api_key']) && filled($data['webhook_secret']);

        return $data;
    }

    public static function whatsapp(): array
    {
        $s = self::settings();

        $data = [
            'enabled'     => (bool) $s?->whatsapp_enabled,
            'host'        => $s?->green_api_host        ?: config('services.green_api.host'),
            'instance_id' => $s?->green_api_instance_id ?: config('services.green_api.instance_id'),
            'token'       => $s?->green_api_token       ?: config('services.green_api.token'),
        ];

        $data['configured'] = filled($data['host']) && filled($data['instance_id']) && filled($data['token']);

        return $data;
    }
}