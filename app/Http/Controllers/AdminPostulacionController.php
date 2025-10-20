<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\GrupoExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Mail\ReciboPagoPostulacion;

class AdminPostulacionController extends Controller
{
    /**
     * GET /admin/postulaciones
     */
    public function index(Request $request)
    {
        $buscar         = trim((string) $request->get('buscar', ''));
        $convocatoriaId = $request->integer('convocatoria_id');
        $estatus        = $request->get('estatus');
        $tipo           = $request->get('tipo');

        // TIPOS permitidos (mismos que en GrupoExamenController)
        $tiposPermitidos = [
            'Examen psicometrico','Examen de conocimiento','entrevista','diplomado',
            'Curso de primeros_auxilios','Cursos AHA','Curso de Capacitacion','otro',
        ];

        // Detectar columna de fecha en perfil_postulantes
        $fechaCol = Schema::hasColumn('perfil_postulantes', 'fecha_nac')
            ? 'fecha_nac'
            : (Schema::hasColumn('perfil_postulantes', 'fecha_nacimiento') ? 'fecha_nacimiento' : null);

        $postulaciones = Postulacion::with([
                'convocatoria:id,titulo',
                'user:id,name,email',

                // Carga segura del perfil
                'user.perfilPostulante' => function ($q) use ($fechaCol) {
                    $sel = ['id','user_id','nombre_completo','telefono','correo_contacto'];
                    if ($fechaCol) {
                        // Exponer la fecha como alias uniforme "fecha_nac" y calcular edad
                        $sel[] = DB::raw("`$fechaCol` as fecha_nac");
                        $sel[] = DB::raw("CASE WHEN `$fechaCol` IS NULL THEN NULL ELSE TIMESTAMPDIFF(YEAR, `$fechaCol`, CURDATE()) END as edad");
                    }
                    $q->select($sel);
                },

                // para el resumen de sesiones en las cards
                'gruposExamen:id,convocatoria_id,tipo,tipo_detalle,fecha_hora,lugar',
            ])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($w) use ($buscar) {
                    $w->whereHas('user', fn ($qq) =>
                            $qq->where('name', 'like', "%{$buscar}%")
                               ->orWhere('email', 'like', "%{$buscar}%"))
                      ->orWhereHas('convocatoria', fn ($qq) =>
                            $qq->where('titulo', 'like', "%{$buscar}%"))
                      ->orWhere('folio', 'like', "%{$buscar}%")
                      ->orWhere('referencia_pago', 'like', "%{$buscar}%");
                });
            })
            ->when($convocatoriaId, fn ($q2) => $q2->where('convocatoria_id', $convocatoriaId))
            ->when($estatus,        fn ($q2) => $q2->where('estatus', $estatus))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Grupos disponibles para el selector (filtrados por convocatoria y opcionalmente por tipo)
        $grupos = GrupoExamen::query()
            ->when($convocatoriaId, fn($q) => $q->where('convocatoria_id', $convocatoriaId))
            ->when(in_array($tipo, $tiposPermitidos, true), fn($q) => $q->where('tipo', $tipo))
            ->orderBy('fecha_hora')
            ->get(['id','convocatoria_id','tipo','tipo_detalle','fecha_hora','lugar','capacidad']);

        if ($buscar || $estatus || $convocatoriaId || $tipo) {
            audit_log('postulaciones.index', 'postulaciones', [
                'buscar' => $buscar,
                'estatus' => $estatus,
                'convocatoria_id' => $convocatoriaId,
                'tipo' => $tipo,
            ]);
        }

        return view('admin.postulaciones.cards_grid_v1', compact(
            'postulaciones','buscar','estatus','convocatoriaId','tipo','grupos'
        ));
    }

    /**
     * GET /admin/postulaciones/{postulacion}
     */
    public function show(Postulacion $postulacion)
    {
        $postulacion->loadMissing([
            'user:id,name,email',
            'user.perfilPostulante', // no seleccionamos columnas específicas aquí (Blade debe usar null-safe)
            'convocatoria:id,titulo,fecha_inicio,fecha_fin',
            'gruposExamen:id,convocatoria_id,tipo,tipo_detalle,fecha_hora,lugar',
        ]);

        audit_log('postulacion.show', $postulacion);

        return view('admin.postulaciones.show', compact('postulacion'));
    }

    /**
     * PATCH /admin/postulaciones/{postulacion}
     * Marcar pagado o cambiar estatus.
     */
    public function update(Request $request, Postulacion $postulacion)
    {
        $nuevoEstatus = $request->string('estatus')->value();
        $marcarPago   = $request->boolean('marcar_pago');

        if ($marcarPago) {
            if ($postulacion->estatus !== Postulacion::ESTATUS_PAGADO) {
                $before = $postulacion->only(['estatus','fecha_pago']);

                $postulacion->estatus    = Postulacion::ESTATUS_PAGADO;
                $postulacion->fecha_pago = $postulacion->fecha_pago ?: now();
                $postulacion->save();

                audit_log('postulacion.mark_paid', $postulacion, [
                    'before' => $before,
                    'after'  => $postulacion->only(['estatus','fecha_pago']),
                ]);

                return back()->with('status', "Folio {$postulacion->folio}: marcado como PAGADO.");
            }

            return back()->with('status', "Folio {$postulacion->folio}: ya estaba pagado.");
        }

        if ($nuevoEstatus) {
            $permitidos = [
                Postulacion::ESTATUS_VALIDADA,
                Postulacion::ESTATUS_RECHAZADA,
            ];

            if (! in_array($nuevoEstatus, $permitidos, true)) {
                return back()->with('status', 'Estatus no permitido.');
            }

            $before = $postulacion->only(['estatus']);
            $postulacion->estatus = $nuevoEstatus;
            $postulacion->save();

            audit_log('postulacion.status_changed', $postulacion, [
                'before' => $before,
                'after'  => $postulacion->only(['estatus']),
            ]);

            return back()->with(
                'status',
                "Folio {$postulacion->folio}: estatus actualizado a " . ucfirst(str_replace('_', ' ', $nuevoEstatus)) . "."
            );
        }

        return back()->with('status', 'Sin cambios.');
    }

    /**
     * POST /admin/postulaciones/{postulacion}/reenviar-recibo
     */
    public function reenviarRecibo(Request $request, Postulacion $postulacion)
    {
        if ($postulacion->estatus !== Postulacion::ESTATUS_PAGADO) {
            return back()->withErrors('No se puede reenviar: pago no confirmado.');
        }

        $p = $postulacion->loadMissing([
            'convocatoria:id,titulo',
            'user:id,name,email',
            'user.perfilPostulante:id,user_id,correo_contacto,nombre_completo',
        ]);

        $correo = $p->user->email ?? optional($p->user?->perfilPostulante)->correo_contacto;

        if (!$correo) {
            return back()->withErrors('No se encontró un correo válido para el aspirante.');
        }

        try {
            $nombre = $p->user->name ?? null;
            Mail::to($correo, $nombre)->queue(new ReciboPagoPostulacion($postulacion->id));

            if (isset($postulacion->recibo_reenvios)) {
                $postulacion->recibo_reenvios = (int) $postulacion->recibo_reenvios + 1;
            }
            if (isset($postulacion->recibo_enviado_at)) {
                $postulacion->recibo_enviado_at = now();
            }
            $postulacion->save();

            audit_log('postulacion.recibo_resent', $postulacion, ['to' => $correo]);

        } catch (\Throwable $e) {
            audit_log('postulacion.recibo_resent_failed', $postulacion, ['error' => $e->getMessage()]);
            return back()->withErrors('No se pudo enviar el correo: '.$e->getMessage());
        }

        return back()->with('status', '📧 Recibo reenviado a '.$correo.' (admin)');
    }


public function validacionMasiva(Request $request)
{
    $buscar         = trim((string) $request->get('buscar', ''));
    $convocatoriaId = $request->integer('convocatoria_id');
    $soloPendientes = $request->boolean('solo_pendientes', true);

    $q = \App\Models\Postulacion::query()
        ->with(['user:id,name,email', 'convocatoria:id,titulo'])
        ->when($convocatoriaId, fn($qq) => $qq->where('convocatoria_id', $convocatoriaId))
        ->when($soloPendientes, fn($qq) => $qq->where('estatus', \App\Models\Postulacion::ESTATUS_PAGO_PENDIENTE ?? 'pago_pendiente'))
        ->when($buscar !== '', function($qq) use ($buscar){
            $qq->where(function($w) use ($buscar){
                $w->where('folio','like',"%{$buscar}%")
                  ->orWhere('referencia_pago','like',"%{$buscar}%")
                  ->orWhereHas('user', fn($u)=>$u->where('name','like',"%{$buscar}%")->orWhere('email','like',"%{$buscar}%"))
                  ->orWhereHas('convocatoria', fn($c)=>$c->where('titulo','like',"%{$buscar}%"));
            });
        })
        ->latest('id');

    $items = $q->paginate(20)->withQueryString();

    $convocatorias = \App\Models\Convocatoria::query()->orderBy('titulo')->get(['id','titulo']);

    return view('admin.postulaciones.validacion_masiva', compact('items','convocatorias','buscar','convocatoriaId','soloPendientes'));
}

public function validarPagosMasivo(Request $request)
{
    $ids           = (array) $request->input('ids', []);
    $folioBanco    = trim((string) $request->input('folio_banco_global', ''));
    $setFechaPago  = $request->boolean('poner_fecha_pago', true);
    $fechaPago     = $request->date('fecha_pago', now());

    if (empty($ids)) {
        return back()->withErrors('Selecciona al menos una postulación.');
    }

    $EST_PAGADO = \App\Models\Postulacion::ESTATUS_PAGADO ?? 'pagado';

    $afectadas = 0;
    \DB::transaction(function () use ($ids, $EST_PAGADO, $folioBanco, $setFechaPago, $fechaPago, &$afectadas) {
        $rows = \App\Models\Postulacion::whereIn('id', $ids)->lockForUpdate()->get();
        foreach ($rows as $p) {
            if ($p->estatus === $EST_PAGADO) {
                continue; // ya estaba pagada
            }
            $p->estatus = $EST_PAGADO;
            if ($setFechaPago && empty($p->fecha_pago)) {
                $p->fecha_pago = $fechaPago;
            }
            if ($folioBanco !== '' && empty($p->folio_banco)) {
                // si necesitas único, puedes validar antes
                $p->folio_banco = $folioBanco;
            }
            $p->save();
            $afectadas++;
        }
    });

    return back()->with('status', "Pagos validados: {$afectadas}.");
}


    /**
     * GET /admin/postulaciones/export
     */
    public function export(Request $request)
    {
        $q = Postulacion::query()
            ->with([
                'user:id,name,email',
                'user.perfilPostulante:id,user_id,nombre_completo,correo_contacto,telefono',
                'convocatoria:id,titulo'
            ])
            ->orderByDesc('id');

        if ($request->filled('convocatoria_id')) {
            $q->where('convocatoria_id', (int) $request->convocatoria_id);
        }

        if ($request->filled('estatus')) {
            $q->where('estatus', $request->estatus);
        }

        if ($request->filled('buscar')) {
            $s = $request->buscar;
            $q->where(function ($qq) use ($s) {
                $qq->whereHas('user', fn ($u) =>
                        $u->where('name',  'like', "%{$s}%")
                          ->orWhere('email','like', "%{$s}%"))
                   ->orWhere('folio', 'like', "%{$s}%")
                   ->orWhere('referencia_pago', 'like', "%{$s}%")
                   ->orWhereHas('convocatoria', fn ($c) =>
                        $c->where('titulo', 'like', "%{$s}%"));
            });
        }

        $filename = 'postulaciones_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        audit_log('postulaciones.export', 'postulaciones', $request->only(['convocatoria_id','estatus','buscar']));

        $callback = function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para Excel

            fputcsv($out, [
                'ID','Convocatoria','Convocatoria ID','Usuario','Email Usuario',
                'Nombre (ficha)','Correo (ficha)','Teléfono (ficha)','Estatus',
                'Folio','Referencia','Fecha pago','Creado','IP','Agente'
            ]);

            $q->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $p) {
                    $perfil = optional($p->user->perfilPostulante);
                    fputcsv($out, [
                        $p->id,
                        $p->convocatoria->titulo ?? '',
                        $p->convocatoria_id,
                        $p->user->name ?? '',
                        $p->user->email ?? '',
                        $perfil->nombre_completo ?? '',
                        $perfil->correo_contacto ?? '',
                        $perfil->telefono ?? '',
                        $p->estatus,
                        $p->folio,
                        $p->referencia_pago,
                        optional($p->fecha_pago)->format('Y-m-d H:i'),
                        optional($p->created_at)->format('Y-m-d H:i'),
                        $p->ip_registro,
                        $p->agente,
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
