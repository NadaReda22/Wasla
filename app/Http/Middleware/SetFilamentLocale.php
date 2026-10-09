<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetFilamentLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('filament_locale')) {
            App::setLocale(Session::get('filament_locale'));
        }
        return $next($request);
    }
}