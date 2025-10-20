<?php

// app/Models/PerfilPostulante.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PerfilPostulante extends Model
{
    protected $table = 'perfil_postulantes';

    protected $fillable = [
        'user_id',
        'nombre_completo','curp','telefono','correo_contacto','correo_alternativo',
        'lugar_nacimiento','preparatoria','promedio_general','sexo','fecha_nacimiento',
        'egreso_prepa_anio','documento_terminacion',
        'tipo_sangre','estado_salud','alergias','medicamentos',
        'contacto_emergencia_nombre','contacto_emergencia_tel',
        'info_adicional',
    ];

    protected $casts = [
        'fecha_nacimiento'  => 'date',
        'promedio_general'  => 'decimal:2',
        'egreso_prepa_anio' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Compat: si alguna vista usa $perfil->fecha_nac
    public function getFechaNacAttribute()
    {
        return $this->fecha_nacimiento;
    }

    // Accessor: calcula edad dinámicamente, no se guarda en BD
    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento
            ? Carbon::parse($this->fecha_nacimiento)->age
            : null;
    }
}
