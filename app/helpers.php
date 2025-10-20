<?php
use App\Models\Audit;

if (! function_exists('audit_log')) {
    /**
     * Registra auditoría SOLO si el usuario actual es admin o subadmin.
     *
     * @param string $action   Ej: 'convocatoria.created', 'settings.updated'
     * @param mixed  $entity   Modelo, id numérico o string identificador
     * @param array  $meta     Metadatos (cambios, filtros, destinatarios, etc.)
     */
    function audit_log(string $action, $entity = null, array $meta = []): void
    {
        $u = auth()->user();
        if (!$u || !in_array($u->role ?? 'aspirante', ['admin','subadmin'])) {
            return; // no registra para aspirantes o invitados
        }

        $entityType = null;
        $entityId   = null;

        if (is_object($entity)) {
            $entityType = get_class($entity);
            if (method_exists($entity, 'getKey')) {
                $entityId = $entity->getKey();
            }
        } elseif (is_numeric($entity)) {
            $entityId = (int) $entity;
        } elseif (is_string($entity) && $entity !== '') {
            $entityType = $entity;
        }

        $req = request();

        Audit::create([
            'user_id'     => $u->id,
            'user_role'   => $u->role,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'meta'        => $meta ?: null,
            'ip'          => $req?->ip(),
            'user_agent'  => $req?->userAgent(),
            'url'         => $req?->fullUrl(),
        ]);
    }
}
