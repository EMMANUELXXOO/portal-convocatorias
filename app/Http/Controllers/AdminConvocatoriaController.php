<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class AdminConvocatoriaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $estatus = $request->get('estatus');

        $items = Convocatoria::withCount('postulaciones')
            ->when($q, fn($qset) =>
                $qset->where(function($w) use ($q){
                    $w->where('titulo','like',"%{$q}%")
                      ->orWhere('descripcion','like',"%{$q}%");
                })
            )
            ->when($estatus, fn($qset) => $qset->where('estatus',$estatus))
            ->latest('fecha_inicio')
            ->paginate(12);

        return view('admin.convocatorias.index', compact('items'));
    }

    public function create()
    {
        $convocatoria = new Convocatoria();
        return view('admin.convocatorias.create', compact('convocatoria'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $allowed   = array_keys($this->validateDataForSnapshot());

        // Payload limpio por whitelist
        $payload = Arr::only($validated, $allowed);

        // Portada
        if ($request->hasFile('portada')) {
           $payload['portada_path'] = Storage::disk('public')
                ->putFile('convocatorias', $request->file('portada'));
        }

        // Galería (si no hay nada, guarda [])
        $payload['galeria_urls'] = $this->buildGaleriaUrls($request) ?? [];

        // Extras
        $payload['carousel_enabled'] = $request->boolean('carousel_enabled');
        $payload['hero_fit']         = $request->input('hero_fit', 'cover');

        // Sanitiza + guard
        $payload = $this->sanitizeForPersistence($payload);
        $this->guardNoNumericKeys($payload, 'store');

        $convocatoria = Convocatoria::create($payload);

        // Auditoría
        $keys  = array_keys($this->validateDataForSnapshot());
        $after = $this->safeSnapshot($convocatoria->only($keys));

        audit_log('convocatoria.created', $convocatoria, [
            'titulo' => $convocatoria->titulo,
            'after'  => $after,
        ]);

        return redirect()
            ->route('admin.convocatorias.index')
            ->with('status', "Convocatoria «{$convocatoria->titulo}» creada.");
    }

    public function edit(Convocatoria $convocatoria)
    {
        return view('admin.convocatorias.edit', compact('convocatoria'));
    }

    public function update(Request $request, Convocatoria $convocatoria)
    {
        $validated = $this->validateData($request);
        $allowed   = array_keys($this->validateDataForSnapshot());

        // Snapshot antes
        $before = $this->safeSnapshot($convocatoria->only($allowed));

        // Payload limpio por whitelist
        $payload = Arr::only($validated, $allowed);

        // Portada
        if ($request->hasFile('portada')) {
           if ($convocatoria->portada_path && !preg_match('~^https?://~i', $convocatoria->portada_path)) {
                Storage::disk('public')->delete($convocatoria->portada_path);
            }
              $payload['portada_path'] = Storage::disk('public')
                ->putFile('convocatorias', $request->file('portada'));
        } else {
            unset($payload['portada_path']);
        }

        // Galería: solo tocar si llegó algo (textarea o archivos)
        $galeria = $this->buildGaleriaUrls($request, true);
        if (!is_null($galeria)) {
            $payload['galeria_urls'] = $galeria;
        } else {
            unset($payload['galeria_urls']);
        }

        // Extras
        $payload['carousel_enabled'] = $request->boolean('carousel_enabled');
        $payload['hero_fit']         = $request->input('hero_fit', 'cover');

        // Sanitiza + guard
        $payload = $this->sanitizeForPersistence($payload);
        $this->guardNoNumericKeys($payload, 'update');

        // Update
        $convocatoria->update($payload);

        // Auditoría después
        $after   = $this->safeSnapshot($convocatoria->only($allowed));
        $changed = array_keys(array_diff_assoc($after, $before));

        audit_log('convocatoria.updated', $convocatoria, [
            'before'  => $before,
            'after'   => $after,
            'changed' => $changed,
        ]);

        return redirect()
            ->route('admin.convocatorias.index')
            ->with('status', "Convocatoria «{$convocatoria->titulo}» actualizada.");
    }

    public function destroy(Convocatoria $convocatoria)
    {
        $titulo = $convocatoria->titulo;
        $id     = $convocatoria->id;

        if ($convocatoria->portada_path) {
            Storage::disk('public')->delete($convocatoria->portada_path);
        }

        // OJO: esto elimina solo la convocatoria, no toca las imágenes de galería.
        $convocatoria->delete();

        audit_log('convocatoria.deleted', ['id' => $id], [
            'titulo' => $titulo,
        ]);

        return back()->with('status', "Convocatoria «{$titulo}» eliminada.");
    }

    /**
     * Elimina UNA imagen de la galería por índice sin borrar la convocatoria.
     * Responde JSON (AJAX) o redirige con flash si es navegación normal.
     */
    public function removeImage(Request $request, Convocatoria $convocatoria, int $index)
    {
        $galeria = $convocatoria->galeria_urls ?? [];

        // Validación de índice
        if (!isset($galeria[$index])) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Índice inválido'], 422);
            }
            return back()->with('error', 'Índice de imagen inválido.');
        }

        // Borrar archivo físico si es local (no URL absoluta)
        $path = $galeria[$index];
        if ($path && !preg_match('~^https?://~i', $path)) {
            Storage::disk('public')->delete($path);
        }

        // Quitar del arreglo y reindexar
        unset($galeria[$index]);
        $convocatoria->galeria_urls = array_values($galeria);
        $convocatoria->save();

        // Auditoría granular
        audit_log('convocatoria.gallery_image_removed', $convocatoria, [
            'removed_index' => $index,
            'removed_path'  => $path,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Imagen eliminada.');
    }

    /**
     * Elimina la portada (y archivo local si aplica) sin tocar la galería.
     */
    public function removeCover(Request $request, Convocatoria $convocatoria)
    {
        $old = $convocatoria->portada_path;

        if ($old) {
            if (!preg_match('~^https?://~i', $old)) {
                Storage::disk('public')->delete($old);
            }
            $convocatoria->portada_path = null;
            $convocatoria->save();

            audit_log('convocatoria.cover_removed', $convocatoria, [
                'removed_path' => $old,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Portada eliminada.');
    }

    // ================== Helpers ==================

    private function validateData(Request $request): array
    {
        return $request->validate([
            'titulo'       => ['required','string','max:255'],
            'descripcion'  => ['required','string'],
            'estatus'      => ['nullable','in:activa,inactiva,borrador,cerrada'],

            'fecha_inicio' => ['nullable','date'],
            'fecha_fin'    => ['nullable','date','after_or_equal:fecha_inicio'],

            // Costos
            'precio_ficha'        => ['required','numeric','min:0'],
            'precio_inscripcion'  => ['required','numeric','min:0'],
            'precio_mensualidad'  => ['required','numeric','min:0'],

            // Cupo
            'cupo_total'   => ['nullable','integer','min:0'],

            // Portada / ubicación
          'portada'      => ['nullable','image','mimes:jpg,jpeg,png,webp','max:3072'],
            'ubicacion'    => ['nullable','string','max:255'],

            // Contacto
            'telefono_1' => ['nullable','string','max:50'],
            'telefono_2' => ['nullable','string','max:50'],
            'correo_1'   => ['nullable','email','max:150'],
            'correo_2'   => ['nullable','email','max:150'],
            'horario_atencion' => ['nullable','string','max:150'],

            // Programa
            'duracion'                    => ['nullable','string','max:100'],
            'certificaciones_adicionales' => ['nullable','string','max:255'],
            'horario_matutino'            => ['nullable','string','max:100'],
            'horario_vespertino'          => ['nullable','string','max:100'],

            // Requisitos / documentos
            'requisitos_generales'         => ['nullable','string'],
            'requisitos_examen_entrevista' => ['nullable','string'],
            'documentos_requeridos'        => ['nullable','string'],

            // Fechas clave
            'fecha_publicacion_resultados' => ['nullable','date'],
            'fecha_inicio_clases'          => ['nullable','date'],

            // Proceso
            'fecha_entrega_solicitudes_inicio' => ['nullable','date'],
            'fecha_entrega_solicitudes_fin'    => ['nullable','date','after_or_equal:fecha_entrega_solicitudes_inicio'],
            'fecha_psicometrico_inicio'        => ['nullable','date'],
            'fecha_psicometrico_fin'           => ['nullable','date','after_or_equal:fecha_psicometrico_inicio'],
            'fecha_entrevistas_inicio'         => ['nullable','date'],
            'fecha_entrevistas_fin'            => ['nullable','date','after_or_equal:fecha_entrevistas_inicio'],
            'fecha_examen_conocimientos'       => ['nullable','date'],
            'fecha_curso_propedeutico_inicio'  => ['nullable','date'],
            'fecha_curso_propedeutico_fin'     => ['nullable','date','after_or_equal:fecha_curso_propedeutico_inicio'],

            // Galería
            'galeria_urls_text'  => ['nullable','string'],
            'galeria_archivos'   => ['nullable','array','max:5'],
            'galeria_archivos.*' => ['nullable','image','max:4096'],

            // Carrusel / fit
            'carousel_enabled' => ['sometimes','boolean'],
            'hero_fit'         => ['nullable','in:cover,contain'],

            // Notas
            'notas' => ['nullable','string'],
        ]);
    }

    /**
     * Combina URLs del textarea y archivos subidos (paths relativos en disco 'public').
     * Si $keepIfEmpty=true y no llega nada, retorna null para no tocar DB en update().
     */
    private function buildGaleriaUrls(Request $request, bool $keepIfEmpty = false): ?array
    {
        $urls = [];

        // 1) URLs del textarea
        $txt = trim((string) $request->input('galeria_urls_text'));
        if ($txt !== '') {
            $partes = preg_split('/\r\n|\n|\r|,/', $txt);
            foreach ($partes as $p) {
                $u = trim($p);
                if ($u !== '') $urls[] = $u;
            }
        }

        // 2) Archivos subidos (name="galeria_archivos[]")
        if ($request->hasFile('galeria_archivos')) {
            foreach ($request->file('galeria_archivos') as $file) {
                if (!$file) continue;
                $path = $file->store('convocatorias/galeria', 'public');
                $urls[] = $path;
            }
        }

        logger('upload-debug', [
            'has'   => $request->hasFile('galeria_archivos'),
            'count' => count((array) $request->file('galeria_archivos')),
            'urls'  => $urls,
        ]);

        $urls = array_values(array_unique(array_filter(array_map('trim', $urls))));

        if ($keepIfEmpty && empty($urls)) {
            return null;
        }
        return $urls;
    }

    /** Claves a incluir en snapshots (y whitelist de columnas). */
    private function validateDataForSnapshot(): array
    {
        return [
            'titulo'       => null,
            'descripcion'  => null,
            'estatus'      => null,
            'fecha_inicio' => null,
            'fecha_fin'    => null,
            'precio_ficha'        => null,
            'precio_inscripcion'  => null,
            'precio_mensualidad'  => null,
            'cupo_total'   => null,
            'portada_path' => null,
            'ubicacion'    => null,
            'telefono_1'   => null,
            'telefono_2'   => null,
            'correo_1'     => null,
            'correo_2'     => null,
            'horario_atencion' => null,
            'duracion'     => null,
            'certificaciones_adicionales' => null,
            'horario_matutino' => null,
            'horario_vespertino'=> null,
            'requisitos_generales' => null,
            'requisitos_examen_entrevista' => null,
            'documentos_requeridos' => null,
            'fecha_publicacion_resultados' => null,
            'fecha_inicio_clases' => null,
            'fecha_entrega_solicitudes_inicio' => null,
            'fecha_entrega_solicitudes_fin'    => null,
            'fecha_psicometrico_inicio'        => null,
            'fecha_psicometrico_fin'           => null,
            'fecha_entrevistas_inicio'         => null,
            'fecha_entrevistas_fin'            => null,
            'fecha_examen_conocimientos'       => null,
            'fecha_curso_propedeutico_inicio'  => null,
            'fecha_curso_propedeutico_fin'     => null,
            'galeria_urls' => null,
            'carousel_enabled' => null,
            'hero_fit'         => null,
            'notas' => null,
        ];
    }

    /** Aplana arrays a JSON para evitar “Array to string conversion” en logs. */
    private function safeSnapshot(array $arr): array
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        return $arr;
    }

    /** Sanea payload y elimina cualquier clave numérica */
    private function sanitizeForPersistence(array $data): array
    {
        unset($data['galeria_archivos'], $data['galeria_urls_text']);

        // Solo claves string
        $data = array_filter($data, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);

        // Whitelist
        $allowed = array_keys($this->validateDataForSnapshot());
        $data    = Arr::only($data, $allowed);

        // Normaliza tipos
        if (isset($data['galeria_urls']) && !is_array($data['galeria_urls'])) {
            $data['galeria_urls'] = (array) $data['galeria_urls'];
        }
        $data['carousel_enabled'] = (bool) ($data['carousel_enabled'] ?? false);
        $data['hero_fit'] = in_array(($data['hero_fit'] ?? 'cover'), ['cover','contain'], true)
            ? $data['hero_fit']
            : 'cover';

        return $data;
    }

    /** Guardia dura: aborta si hay claves numéricas en el payload */
    private function guardNoNumericKeys(array $data, string $ctx): void
    {
        $numeric = array_filter(array_keys($data), fn($k) => is_int($k) || ctype_digit((string) $k));
        if (!empty($numeric)) {
            logger()->error('Payload con claves numéricas', [
                'ctx'  => $ctx,
                'keys' => array_keys($data),
            ]);
            abort(422, 'Payload inválido (claves numéricas detectadas).');
        }
    }
}
