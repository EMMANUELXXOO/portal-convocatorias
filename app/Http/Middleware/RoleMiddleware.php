<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    // Uso: ->middleware('role:admin,subadmin')
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) abort(403);

        // Compatibilidad con is_admin si existiera
        $currentRole = $user->role ?? (($user->is_admin ?? false) ? 'admin' : 'aspirante');

        if (! in_array($currentRole, $roles, true)) {
            abort(403, 'No autorizado.');
        }
        return $next($request);
    }
}
