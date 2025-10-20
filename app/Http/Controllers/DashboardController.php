<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Postulacion;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Últimas 3 postulaciones
        $postulaciones = $user->postulaciones()
            ->with(['convocatoria:id,titulo,fecha_publicacion_resultados'])
            ->latest('id')
            ->take(3)
            ->get();

        // Paso actual
        $perfilCompleto = (bool) $user->perfilPostulante;

        $ultima = $user->postulaciones()
            ->with('convocatoria')
            ->latest('id')
            ->first();

        $EST_PAGADO     = Postulacion::ESTATUS_PAGADO ?? 'pagado';
        $pagoCompletado = $ultima && $ultima->estatus === $EST_PAGADO;

        // ========= Fechas asignadas (sin asumir nombres de columnas) =========
        $fechasAsignadas = collect();

        if ($ultima) {
            // Trae todas las columnas del grupo para no fallar por nombres
            $rows = DB::table('grupo_postulacion')
                ->join('grupos_examen', 'grupos_examen.id', '=', 'grupo_postulacion.grupo_examen_id')
                ->where('grupo_postulacion.postulacion_id', $ultima->id)
                ->get(['grupos_examen.*']);

            // Posibles nombres para fecha y lugar (ajusta si tienes otros)
            $dateCols  = ['fecha', 'inicio', 'fecha_hora', 'fecha_inicio', 'programado_en', 'starts_at', 'scheduled_at'];
            $placeCols = ['lugar', 'aula', 'salon', 'sede', 'ubicacion', 'site'];

            $fechasAsignadas = collect($rows)->map(function ($r) use ($dateCols, $placeCols) {
                // elegir la primera columna de fecha que exista y tenga valor
                $rawWhen = null;
                foreach ($dateCols as $col) {
                    if (property_exists($r, $col) && !empty($r->{$col})) {
                        $rawWhen = $r->{$col};
                        break;
                    }
                }

                // si no hay fecha, descartar el registro
                if (!$rawWhen) {
                    return null;
                }

                $when = $rawWhen instanceof \DateTimeInterface
                    ? Carbon::instance($rawWhen)
                    : Carbon::parse($rawWhen);

                // elegir la primera columna de lugar disponible
                $lugar = null;
                foreach ($placeCols as $col) {
                    if (property_exists($r, $col) && !empty($r->{$col})) {
                        $lugar = $r->{$col};
                        break;
                    }
                }

                return (object) [
                    'tipo'  => strtolower($r->tipo ?? 'actividad'),
                    'when'  => $when,
                    'lugar' => $lugar,
                ];
            })
            ->filter()                // elimina nulos (sin fecha)
            ->sortBy('when')          // orden cronológico en PHP
            ->values();
        }

        // Steps para la línea de tiempo
        $resultadosFecha  = optional(optional($ultima)->convocatoria)->fecha_publicacion_resultados;
        $resultadosPublic = $resultadosFecha ? $resultadosFecha->isPast() : false;

        $steps = [
            ['key'=>'registro',    'label'=>'Registro',    'done'=>true],
            ['key'=>'perfil',      'label'=>'Perfil',      'done'=>$perfilCompleto],
            ['key'=>'postulacion', 'label'=>'Postulación', 'done'=>(bool) $ultima],
            ['key'=>'pago',        'label'=>'Pago',        'done'=>$pagoCompletado],
            ['key'=>'examen',      'label'=>'Fechas',      'done'=>$fechasAsignadas->isNotEmpty()],
            ['key'=>'resultados',  'label'=>'Resultados',  'done'=>$resultadosPublic],
        ];

        return view('dashboard', [
            'postulaciones'   => $postulaciones,
            'steps'           => $steps,
            'ultima'          => $ultima,
            'fechasAsignadas' => $fechasAsignadas,
        ]);
    }
}
