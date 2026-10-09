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

        // 2.this to force secure asset links over ngrok
       // Force HTTPS if accessed via ngrok or production
        if (str_contains(request()->getHost(), 'ngrok') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
