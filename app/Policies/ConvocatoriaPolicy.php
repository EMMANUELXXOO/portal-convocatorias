<?php

namespace App\Policies;

use App\Models\Convocatoria;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConvocatoriaPolicy
{
    /**
     * Admin siempre permitido.
     */
    public function before(User $user, string $ability)
    {
        if ($user->can('admin')) {
            return true;
        }
    }

    /**
     * ¿El usuario puede postular a esta convocatoria?
     * Reglas:
     *  - Debe estar vigente por fechas.
     *  - (Opcional) Debe estar activa por estatus si existe ese campo.
     *  - (Opcional) Debe tener cupo si existen campos de cupo.
     *  - (Opcional) No permitir si ya existe postulación previa (mejor UX).
     */
    public function postular(User $user, Convocatoria $convocatoria): Response
    {
        $now = now();

        // Fechas
        if ($convocatoria->fecha_inicio && $now->lt($convocatoria->fecha_inicio)) {
            return Response::deny('La convocatoria aún no inicia.');
        }
        if ($convocatoria->fecha_fin && $now->gt($convocatoria->fecha_fin)) {
            return Response::deny('La convocatoria ya finalizó.');
        }

        // Estatus (si tu modelo lo maneja)
        if (property_exists($convocatoria, 'estatus') || isset($convocatoria->estatus)) {
            // Ajusta el valor esperado a tu enum/constante real
            $estatusActiva = defined('\App\Models\Convocatoria::ESTATUS_ACTIVA')
                ? \App\Models\Convocatoria::ESTATUS_ACTIVA
                : 'activa';

            if ($convocatoria->estatus !== $estatusActiva) {
                return Response::deny('La convocatoria no está activa.');
            }
        }

        // Cupo (si manejas cupo_total / cupo_ocupado)
        if (isset($convocatoria->cupo_total) && isset($convocatoria->cupo_ocupado)) {
            $disponibles = (int)$convocatoria->cupo_total - (int)$convocatoria->cupo_ocupado;
            if ($disponibles <= 0) {
                return Response::deny('La convocatoria no tiene cupo disponible.');
            }
        }

        // Ya postuló (opcional, mejora el mensaje en vez de depender del unique)
        $yaPostulo = Postulacion::where('user_id', $user->id)
            ->where('convocatoria_id', $convocatoria->id)
            ->exists();

        if ($yaPostulo) {
            return Response::deny('Ya registraste una postulación para esta convocatoria.');
        }

        return Response::allow();
    }
}
