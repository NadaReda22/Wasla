<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            $settings = Setting::first();
            View::share('settings', $settings);
        }

        // Force HTTPS if accessed via ngrok, cloudflare tunnels, or production
        $host = request()->getHost();
        if (str_contains($host, 'ngrok') || str_contains($host, 'trycloudflare.com') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
