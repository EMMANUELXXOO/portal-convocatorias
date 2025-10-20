<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Models & Policies
use App\Models\Postulacion;
use App\Policies\PostulacionPolicy;
use App\Models\Convocatoria;
use App\Policies\ConvocatoriaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Postulacion::class  => PostulacionPolicy::class,
        Convocatoria::class => ConvocatoriaPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Admin pleno
        Gate::define('admin', fn($user) => ($user->role ?? 'aspirante') === 'admin');

        // Backoffice (admin o subadmin)
        Gate::define('backoffice', fn($user) => in_array($user->role ?? 'aspirante', ['admin','subadmin']));

        // Gestión de usuarios (admin y subadmin)
        Gate::define('manage-users', fn($user) => in_array($user->role ?? 'aspirante', ['admin','subadmin']));

        // Configuración (solo admin)
        Gate::define('manage-settings', fn($user) => ($user->role ?? 'aspirante') === 'admin');

        // Aspirante (NO admin ni subadmin) — usado en tus rutas con can:no-admin
        Gate::define('no-admin', fn($user) => ! in_array($user->role ?? 'aspirante', ['admin','subadmin']));
    }
}
