<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NotificacionFechaExamen;
use App\Models\Convocatoria;
use App\Models\GrupoExamen;
use App\Models\Postulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrupoExamenController extends Controller
{
    /** Tipos permitidos (centralizado) */
    private array $tipos = [
        'Examen psicometrico',
        'Examen de conocimiento',
        'entrevista Presencial',
        'diplomado',
        'primeros_auxilios',
        'aha',
        'capacitacion',
        'otro',
    ];

    // -------- LISTAR --------
    public function index(Request $request)
    {
        $q = GrupoExamen::with(['convocatoria'])
            ->withCount(['postulaciones as ocupados'])
            ->when($request->convocatoria_id, fn($qq) =>
                $qq->where('convocatoria_id', $request->convocatoria_id))
            ->orderBy('fecha_hora', 'asc')
            ->paginate(20)
            ->withQueryString();

        $convocatorias = Convocatoria::orderBy('id','desc')->get();

        return view('admin.grupos.index', compact('q','convocatorias'));
    }

    // -------- CREAR --------
    public function create()
    {
        $convocatorias = Convocatoria::orderBy('id','desc')->get();

        return view('admin.grupos.create', [
            'convocatorias' => $convocatorias,
            'tipos'         => $this->tipos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'convocatoria_id' => ['required','exists:convocatorias,id'],
            'tipo'            => ['required', Rule::in($this->tipos)],
            'tipo_detalle'    => ['nullable','string','max:120', Rule::requiredIf(fn() => $request->tipo === 'otro')],
            'fecha_hora'      => ['required','date'],
            'lugar'           => ['nullable','string','max:255'],
            'capacidad'       => ['required','integer','min:1','max:5000'],
        ]);

        if (($data['tipo'] ?? null) !== 'otro') {
            $data['tipo_detalle'] = null;
        }

        GrupoExamen::create($data);

        return redirect()->route('admin.grupos.index')->with('status','Grupo creado.');
    }

    // -------- EDITAR --------
    public function edit(GrupoExamen $grupo)
    {
        $convocatorias = Convocatoria::orderBy('id','desc')->get();

        return view('admin.grupos.edit', [
            'grupo'         => $grupo,
            'convocatorias' => $convocatorias,
            'tipos'         => $this->tipos,
        ]);
    }

    public function update(Request $request, GrupoExamen $grupo)
    {
        $data = $request->validate([
            'convocatoria_id' => ['required','exists:convocatorias,id'],
            'tipo'            => ['required', Rule::in($this->tipos)],
            'tipo_detalle'    => ['nullable','string','max:120', Rule::requiredIf(fn() => $request->tipo === 'otro')],
            'fecha_hora'      => ['required','date'],
            'lugar'           => ['nullable','string','max:255'],
            'capacidad'       => ['required','integer','min:1','max:5000'],
        ]);

        if (($data['tipo'] ?? null) !== 'otro') {
            $data['tipo_detalle'] = null;
        }

        $grupo->update($data);

        return back()->with('status','Grupo actualizado.');
    }

    public function destroy(GrupoExamen $grupo)
    {
        $grupo->delete();
        return back()->with('status','Grupo eliminado.');
    }

    // -------- ASIGNAR (UI por grupo) --------
    public function asignarForm(GrupoExamen $grupo, Request $request)
    {
        $postulaciones = Postulacion::with('user')
            ->where('convocatoria_id', $grupo->convocatoria_id)
            ->whereDoesntHave('gruposExamen', function($q) use ($grupo){
                $q->where('tipo', $grupo->tipo);
            })
            ->orderBy('id','desc')
            ->paginate(30)
            ->withQueryString();

        return view('admin.grupos.asignar', compact('grupo','postulaciones'));
    }

    // -------- ASIGNACIÓN MASIVA (desde listado) --------
    public function asignarStore(Request $request, GrupoExamen $grupo)
    {
        $data = $request->validate([
            'postulacion_ids'   => ['required','array','min:1'],
            'postulacion_ids.*' => ['integer','distinct','exists:postulaciones,id'],
        ]);

        $postulaciones = Postulacion::query()
            ->whereIn('id', $data['postulacion_ids'])
            ->where('convocatoria_id', $grupo->convocatoria_id)
            ->get();

        if ($postulaciones->isEmpty()) {
            return back()->withErrors('No hay postulaciones válidas para asignar.');
        }

        $yaAsignados = $grupo->postulaciones()
            ->whereIn('postulaciones.id', $postulaciones->pluck('id'))
            ->pluck('postulaciones.id')
            ->all();

        $capacidad = (int) $grupo->capacidad;
        $ocupados  = (int) $grupo->postulaciones()->count();
        $libres    = max($capacidad - $ocupados, 0);

        $porAsignar = $postulaciones->whereNotIn('id', $yaAsignados)->pluck('id')->values();

        if ($porAsignar->isEmpty()) {
            return back()->with('status', 'Nada que asignar (ya estaban asignados o no aplican).');
        }

        if ($porAsignar->count() > $libres) {
            return back()->withErrors("Capacidad insuficiente. Libres: {$libres}, seleccionados: {$porAsignar->count()}.");
        }

        $grupo->postulaciones()->syncWithoutDetaching($porAsignar->all());

        return back()->with('status', 'Asignación realizada: '.$porAsignar->count().' aspirante(s).');
    }

    // -------- DESASIGNAR --------
    public function desasignar(GrupoExamen $grupo, Postulacion $postulacion)
    {
        $grupo->postulaciones()->detach($postulacion->id);
        return back()->with('status','Aspirante desasignado.');
    }

    // -------- DESASIGNACIÓN MASIVA EN UN GRUPO --------
    public function desasignarMasivo(GrupoExamen $grupo, Request $request)
    {
        $payload = $request->validate([
            'postulacion_ids'   => ['required','array','min:1'],
            'postulacion_ids.*' => ['integer','exists:postulaciones,id'],
        ]);

        $ids = $grupo->postulaciones()
            ->whereIn('postulaciones.id', $payload['postulacion_ids'])
            ->pluck('postulaciones.id')
            ->all();

        if (empty($ids)) {
            return back()->withErrors('Ninguna selección pertenece a este grupo.');
        }

        $grupo->postulaciones()->detach($ids);

        return back()->with('status', 'Desasignados: '.count($ids));
    }

    // -------- LISTA DE ASIGNADOS --------
    public function asignados(GrupoExamen $grupo)
    {
        $grupo->loadMissing([
            'convocatoria:id,titulo',
            'postulaciones:id,user_id,convocatoria_id',
            'postulaciones.user:id,name,email',
            'postulaciones.user.perfilPostulante:id,user_id,nombre_completo,telefono,correo_contacto',
        ]);

        $postulaciones = $grupo->postulaciones->sortBy(function ($p) {
            return mb_strtolower($p->user->perfilPostulante->nombre_completo ?? $p->user->name ?? '');
        })->values();

        return view('admin.grupos.asignados', compact('grupo','postulaciones'));
    }

    // -------- NOTIFICAR POR CORREO --------
    public function notificarSeleccion(Request $request, GrupoExamen $grupo)
    {
        // Normaliza método por si llegó DELETE accidental
        if (strtoupper($request->getMethod()) !== 'POST') {
            $request->request->remove('_method');
        }

        $mensaje = (string) $request->input('mensaje', '');
        $ids     = (array) $request->input('postulacion_ids', []);

        // ✅ Tira de la relación del grupo (pivot), no de una columna directa
        $q = $grupo->postulaciones()->with(['user.perfilPostulante']);
        if (!empty($ids)) {
            $q->whereIn('postulaciones.id', $ids);
        }
        $postulaciones = $q->get();

        if ($postulaciones->isEmpty()) {
            return back()->withErrors('No hay destinatarios para notificar.');
        }

        $destinatarios = [];
        $descartados   = [];

        foreach ($postulaciones as $p) {
            $perfil = optional($p->user->perfilPostulante);

            // Preferencia: correo de contacto de ficha, si no, email del usuario
            $raw = $perfil->correo_contacto ?: $p->user->email;

            // Normalización agresiva de espacios invisibles y basura
            $email = (string) $raw;
            $email = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $email);
            $email = preg_replace('/[\r\n\t]+/u', '', $email);
            $email = preg_replace('/\s+/u', '', trim($email));

            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);

            if ($email && $isValid) {
                $destinatarios[] = ['p' => $p, 'email' => $email];
            } else {
                $descartados[] = [
                    'postulacion_id' => $p->id,
                    'raw'    => $raw,
                    'normal' => $email,
                    'motivo' => 'Email vacío o formato inválido'
                ];
            }
        }

        if (empty($destinatarios)) {
            Log::warning('Notificar grupo: cero correos válidos', [
                'grupo_id'    => $grupo->id,
                'descartados' => $descartados,
            ]);
            $detalle = collect($descartados)->take(3)->map(fn($d) => "#{$d['postulacion_id']}: {$d['normal']}")->implode(', ');
            return back()->withErrors('No se pudo enviar ningún correo (verifica correos válidos). '.$detalle);
        }

        // Envía sin colas para pruebas (send). Luego podrás pasar a queue().
        $enviados = 0;
        $fallidos = [];
        foreach ($destinatarios as $d) {
            try {
                Mail::to($d['email'])->send(new NotificacionFechaExamen($grupo, $d['p'], $mensaje));
                $enviados++;
            } catch (\Throwable $e) {
                $fallidos[] = ['postulacion_id' => $d['p']->id, 'email' => $d['email'], 'error' => $e->getMessage()];
            }
        }

        if ($fallidos) {
            Log::warning('Notificar grupo: envíos fallidos', ['grupo_id' => $grupo->id, 'fallidos' => $fallidos]);
        }

        $msg = "Correos enviados: {$enviados}.";
        if ($fallidos) { $msg .= ' Fallidos: '.count($fallidos).'.'; }

        return back()->with('status', $msg);
    }

    // -------- EXPORTAR ASISTENCIA (CSV) --------
    public function exportAsistencia(GrupoExamen $grupo): StreamedResponse
    {
        $grupo->loadMissing(['postulaciones.user.perfilPostulante', 'convocatoria:id,titulo']);

        $filename = "asistencia_grupo_{$grupo->id}_" . now()->format('Ymd_His') . ".csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($grupo) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel

            fputcsv($out, [
                'Grupo ID','Tipo','Detalle','Convocatoria','Fecha-Hora','Lugar',
                'Postulación ID','Usuario','Email','Nombre ficha','Teléfono'
            ]);

            foreach ($grupo->postulaciones as $p) {
                $perfil = optional($p->user->perfilPostulante);
                fputcsv($out, [
                    $grupo->id,
                    $grupo->tipo,
                    $grupo->tipo_detalle ?? '',
                    $grupo->convocatoria->titulo ?? '',
                    optional($grupo->fecha_hora)->timezone('America/Tijuana')->format('Y-m-d H:i'),
                    $grupo->lugar ?? '',
                    $p->id,
                    $p->user->name ?? '',
                    $p->user->email ?? '',
                    $perfil->nombre_completo ?? '',
                    $perfil->telefono ?? '',
                ]);
            }

            fclose($out);
        };

        if (function_exists('audit_log')) {
            audit_log('grupos.export_asistencia', $grupo);
        }

        return response()->stream($callback, 200, $headers);
    }
}
