<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Convocatoria extends Model
{
    protected $fillable = [
        'titulo','descripcion','estatus','fecha_inicio','fecha_fin',
        'precio_ficha','precio_inscripcion','precio_mensualidad','cupo_total',
        'portada_path','ubicacion','telefono_1','telefono_2','correo_1','correo_2','horario_atencion',
        'duracion','certificaciones_adicionales','horario_matutino','horario_vespertino',
        'requisitos_generales','requisitos_examen_entrevista','documentos_requeridos',
        'fecha_publicacion_resultados','fecha_inicio_clases',
        'fecha_entrega_solicitudes_inicio','fecha_entrega_solicitudes_fin',
        'fecha_psicometrico_inicio','fecha_psicometrico_fin',
        'fecha_entrevistas_inicio','fecha_entrevistas_fin',
        'fecha_examen_conocimientos',
        'fecha_curso_propedeutico_inicio','fecha_curso_propedeutico_fin',
        'galeria_urls','notas',
        'carousel_enabled','hero_fit',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_publicacion_resultados' => 'date',
        'fecha_inicio_clases' => 'date',
        'fecha_entrega_solicitudes_inicio' => 'date',
        'fecha_entrega_solicitudes_fin' => 'date',
        'fecha_psicometrico_inicio' => 'date',
        'fecha_psicometrico_fin' => 'date',
        'fecha_entrevistas_inicio' => 'date',
        'fecha_entrevistas_fin' => 'date',
        'fecha_examen_conocimientos' => 'date',
        'fecha_curso_propedeutico_inicio' => 'date',
        'fecha_curso_propedeutico_fin' => 'date',

        'galeria_urls' => 'array',     // ← deja que Laravel serialice JSON
        'carousel_enabled' => 'boolean',
    ];

    protected $appends = [
        'portada_url',
        'galeria_urls_public',
        'carousel_slides',
    ];

    // ---------- Accessors de solo lectura ----------
    public function getPortadaUrlAttribute(): ?string
    {
        if (!$this->portada_path) return null;
        if (preg_match('~^https?://~i', $this->portada_path)) {
            return $this->portada_path;
        }
        return Storage::disk('public')->url($this->portada_path);
    }

    public function getGaleriaUrlsPublicAttribute(): array
    {
        $arr = (array) ($this->galeria_urls ?? []);
        $out = [];
        foreach ($arr as $p) {
            if (!is_string($p) || $p === '') continue;
            $out[] = preg_match('~^https?://~i', $p)
                ? $p
                : Storage::disk('public')->url($p);
        }
        return $out;
    }

   public function getCarouselSlidesAttribute(): array
{
    // Si quieres respetar el switch:
    if (!$this->carousel_enabled) {
        return [];
    }

    // Solo usa las imágenes subidas para el carrusel (galeria_urls)
    $galeria = array_filter((array) $this->galeria_urls_public);

    // Máximo 5
    return array_slice($galeria, 0, 5);
}


    // Relaciones (igual que las tenías)
    public function postulaciones()
    {
        return $this->hasMany(\App\Models\Postulacion::class);
    }
    public function gruposExamen()
    {
        return $this->hasMany(\App\Models\GrupoExamen::class);
    }
}
