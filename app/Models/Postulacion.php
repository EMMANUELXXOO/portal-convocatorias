<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulaciones';

    // ===== Estados centralizados =====
    public const ESTATUS_PENDIENTE       = 'pendiente';
    public const ESTATUS_PAGO_PENDIENTE  = 'pago_pendiente';
    public const ESTATUS_PAGADO          = 'pagado';
    public const ESTATUS_VALIDADA        = 'validada';
    public const ESTATUS_RECHAZADA       = 'rechazada';

    public const ESTADOS = [
        self::ESTATUS_PENDIENTE,
        self::ESTATUS_PAGO_PENDIENTE,
        self::ESTATUS_PAGADO,
        self::ESTATUS_VALIDADA,
        self::ESTATUS_RECHAZADA,
    ];

    // ===== Asignación masiva =====
    protected $fillable = [
        'user_id',
        'convocatoria_id',
        'estatus',
        'folio',
        'referencia_pago',
        'folio_banco',
        'fecha_pago',
        'ip_registro',
        'agente',

        // Métricas de recibo
        'recibo_reenvios',
        'recibo_enviado_at',

        // Control de edición de usuario
        'user_edit_count',
        'user_first_edit_at',
    ];

    protected $casts = [
        'fecha_pago'         => 'datetime',
        'recibo_enviado_at'  => 'datetime',
        'user_first_edit_at' => 'datetime',
        'recibo_reenvios'    => 'integer',
        'user_edit_count'    => 'integer',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    // Si quieres exponer estos campos en arrays/JSON
    protected $appends = [
        'estatus_label',
        'es_editable_por_usuario',
        'puede_eliminar_por_usuario',
        // 'grupo_psicometrico',
        // 'grupo_conocimiento',
    ];

    // ===== Relaciones =====
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }

    /**
     * Grupos/sesiones asignados a esta postulación.
     * Tabla pivote: grupo_postulacion (grupo_examen_id, postulacion_id, asignado_en, timestamps)
     */
    public function gruposExamen(): BelongsToMany
    {
        return $this->belongsToMany(GrupoExamen::class, 'grupo_postulacion')
            ->withPivot('asignado_en')
            ->withTimestamps();
    }

    /** Filtros por tipo de grupo (para consultas puntuales) */
    public function grupoPsicometrico(): BelongsToMany
    {
        return $this->gruposExamen()->where('tipo', 'psicometrico');
    }

    public function grupoConocimiento(): BelongsToMany
    {
        return $this->gruposExamen()->where('tipo', 'conocimiento');
    }

    // ===== Scopes =====
    public function scopeEstatus($query, string $estatus)
    {
        return $query->where('estatus', $estatus);
    }

    public function scopeDeConvocatoria($query, int $convocatoriaId)
    {
        return $query->where('convocatoria_id', $convocatoriaId);
    }

    public function scopeDelUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ===== Helpers de dominio =====
    public function marcarPagado(): void
    {
        $this->estatus = self::ESTATUS_PAGADO;
        if (is_null($this->fecha_pago)) {
            $this->fecha_pago = now();
        }
        $this->save();
    }

    public function estaPagado(): bool
    {
        return $this->estatus === self::ESTATUS_PAGADO;
    }

    // ===== Accessors / Mutators =====
    public function getEstatusLabelAttribute(): string
    {
        return match ($this->estatus) {
            self::ESTATUS_PENDIENTE       => 'Pendiente',
            self::ESTATUS_PAGO_PENDIENTE  => 'Pago pendiente',
            self::ESTATUS_PAGADO          => 'Pagado',
            self::ESTATUS_VALIDADA        => 'Validada',
            self::ESTATUS_RECHAZADA       => 'Rechazada',
            default                       => ucfirst((string)$this->estatus),
        };
    }

    /** Reglas para UI (habilitar/ocultar acciones al usuario) */
    public function getEsEditablePorUsuarioAttribute(): bool
    {
        return (int)($this->user_edit_count ?? 0) < 1
            && !in_array($this->estatus, [self::ESTATUS_PAGADO, self::ESTATUS_VALIDADA], true);
    }

    public function getPuedeEliminarPorUsuarioAttribute(): bool
    {
        return !in_array($this->estatus, [self::ESTATUS_PAGADO, self::ESTATUS_VALIDADA], true);
    }

    /**
     * Accessors para uso cómodo en Blade:
     * - $postulacion->grupo_psicometrico
     * - $postulacion->grupo_conocimiento
     * Si ya están eager-loaded en 'gruposExamen', no hará consultas extra.
     */
    public function getGrupoPsicometricoAttribute()
    {
        return $this->relationLoaded('gruposExamen')
            ? $this->gruposExamen->firstWhere('tipo', 'psicometrico')
            : $this->grupoPsicometrico()->first();
    }

    public function getGrupoConocimientoAttribute()
    {
        return $this->relationLoaded('gruposExamen')
            ? $this->gruposExamen->firstWhere('tipo', 'conocimiento')
            : $this->grupoConocimiento()->first();
    }
}
