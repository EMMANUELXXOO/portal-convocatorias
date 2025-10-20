<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Reenvío de recibo: 3 intentos / 10 minutos por usuario/IP
        RateLimiter::for('mail-resend', function (Request $request) {
            return Limit::perMinutes(10, 3)->by(optional($request->user())->id ?: $request->ip());
        });

        // Prueba SMTP: 3 intentos / 10 minutos por usuario/IP
        RateLimiter::for('test-email', function (Request $request) {
            return Limit::perMinutes(10, 3)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
