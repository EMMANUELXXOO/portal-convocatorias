<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoExamen extends Model
{
    use HasFactory;

    protected $table = 'grupos_examen';

   // app/Models/GrupoExamen.php
protected $fillable = [
    'convocatoria_id',
    'tipo',
    'tipo_detalle',  
    'fecha_hora',
    'lugar',
    'capacidad',
];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'capacidad'  => 'integer',
    ];

    // Relaciones
    public function convocatoria() {
        return $this->belongsTo(\App\Models\Convocatoria::class);
    }

  public function postulaciones()
{
    return $this->belongsToMany(\App\Models\Postulacion::class, 'grupo_postulacion')
        ->withPivot('asignado_en')
        ->withTimestamps(); // ← importante
}


    // Helpers
    public function getOcupadosAttribute(): int {
        return (int) $this->postulaciones()->count();
    }

    public function getCupoDisponibleAttribute(): int {
        $cap = (int) ($this->capacidad ?? 0);
        return max(0, $cap - $this->ocupados);
    }

    // Scopes
    public function scopeDeConvocatoria($q, int $convocatoriaId) {
        return $q->where('convocatoria_id', $convocatoriaId);
    }

    public function scopeTipo($q, string $tipo) {
        return $q->where('tipo', $tipo);
    }
}
