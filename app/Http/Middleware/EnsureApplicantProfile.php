<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApplicantProfile
{
    public function handle(Request $request, Closure $next)
    {
        // Usuario debe estar autenticado y tener perfil de aspirante
        if (! $request->user() || ! $request->user()->perfilPostulante) {
            return redirect()
                ->route('perfil.create')
                ->with('status', 'Completa tu ficha para continuar.');
        }

        return $next($request);
    }
}
