<?php

namespace App\Policies;

use App\Models\Postulacion;
use App\Models\User;

class PostulacionPolicy
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

    public function view(User $user, Postulacion $postulacion): bool
    {
        return $user->id === $postulacion->user_id;
    }

    public function update(User $user, Postulacion $postulacion): bool
    {
        // Regla simple: solo el dueño.
        // Si quieres endurecer: permitir solo si estatus ≠ PAGADO.
        return $user->id === $postulacion->user_id;
    }

    public function delete(User $user, Postulacion $postulacion): bool
    {
        // Recomendado: permitir borrar solo si no está pagado.
        if ($user->id !== $postulacion->user_id) {
            return false;
        }

        $estatusPagado = defined('\App\Models\Postulacion::ESTATUS_PAGADO')
            ? \App\Models\Postulacion::ESTATUS_PAGADO
            : 'pagado';

        return $postulacion->estatus !== $estatusPagado;
    }
}
