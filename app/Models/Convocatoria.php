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
   public function getPortadaUrlAttribute(): string
    {
       $placeholder = asset('images/convocatoria-placeholder.svg');
        $path = $this->portada_path;

        if (!$path) {
            return $placeholder;
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            return $placeholder;
        }

        $url = $disk->url($path);

        if ($this->updated_at) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator.'v='.$this->updated_at->timestamp;
        }

        return $url;
    }

    public function getGaleriaUrlsPublicAttribute(): array
    {
        $arr = (array) ($this->galeria_urls ?? []);
        $out = [];
        $disk = Storage::disk('public');
        foreach ($arr as $p) {
            if (!is_string($p) || $p === '') continue;
          if (preg_match('~^https?://~i', $p)) {
                $out[] = $p;
                continue;
            }
            if (!$disk->exists($p)) {
                continue;
            }

            $url = $disk->url($p);
            if ($this->updated_at) {
                $separator = str_contains($url, '?') ? '&' : '?';
                $url .= $separator.'v='.$this->updated_at->timestamp;
            }

            $out[] = $url;
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
