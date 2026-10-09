<?php

namespace App\Providers\Filament;

use App\Models\Setting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Fetch your dynamic settings safely
        $settings = Setting::first();
        $brandLogo = $settings?->brand_logo ? \Illuminate\Support\Facades\Storage::url($settings->brand_logo) : asset('images/logo.png');
        
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->darkMode(false)
            ->colors([
                'primary' => Color::Blue,
                'danger'  => Color::Red,
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            
            // Topbar custom hook
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn () => view('filament.hooks.custom-topbar')
            )
            // Leaflet CSS
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />'
            )
            // Leaflet JS + map initialiser
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.hooks.coverage-map-scripts')
            )
            
            // Custom theme
            ->theme(asset('css/filament/admin/theme.css'))
            ->sidebarCollapsibleOnDesktop()
            
            // Crash-proof dynamic brand name
            ->brandName(fn () => rescue(fn () => Setting::first()?->brand_name, 'Wasla'))
            ->brandLogo(fn () => view('filament.logo'))
            // Set your dynamic logo for the browser tab favicon
            ->favicon($brandLogo)
            
            // Resources & pages discovery
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            
            // Sidebar Navigation Group Ordering
            ->navigationGroups([
                'Operations',
                'Finance',
                'Settings',
            ])
            
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetFilamentLocale::class, // <-- ADDED CUSTOM MIDDLEWARE HERE
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}