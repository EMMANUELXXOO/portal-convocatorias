<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // (Opcional) tus rate limiters
        RateLimiter::for('mail-resend', function (Request $request) {
            return [
               Limit::perMinutes(10, 3)->by($key),  // máx 3 cada 10 minutos
        Limit::perHour(20)->by($key),        // máx 20 por hora (si tu versión lo soporta)
    ];
        });

        // 👇 Fuerza alias de middleware
        app('router')->aliasMiddleware(
            'profile.completed',
            \App\Http\Middleware\EnsureApplicantProfile::class
        );
    }
}
