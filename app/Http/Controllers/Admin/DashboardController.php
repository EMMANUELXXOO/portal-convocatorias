<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Postulacion;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->startOfDay();

        // ====== 1) KPIs ======
        $totalPostulacionesActivas = Postulacion::query()
            ->join('convocatorias as c', 'c.id', '=', 'postulaciones.convocatoria_id')
            ->where('c.estatus', 'activa')
            ->where(function ($q) use ($today) {
                $q->whereNull('c.fecha_inicio')->orWhere('c.fecha_inicio', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('c.fecha_fin')->orWhere('c.fecha_fin', '>=', $today);
            })
            ->count();

        // ====== 2) Embudo (conteos por etapa) ======
        $EST_PEND   = Postulacion::ESTATUS_PENDIENTE       ?? 'pendiente';
        $EST_P_PEND = Postulacion::ESTATUS_PAGO_PENDIENTE  ?? 'pago_pendiente';
        $EST_PAGADO = Postulacion::ESTATUS_PAGADO          ?? 'pagado';

        $totalRegistro   = DB::table('users')->count();
        $totalPerfil     = DB::table('perfil_postulantes')->count();
        $totalPostul     = Postulacion::count();
        $totalPago       = Postulacion::where('estatus', $EST_PAGADO)->count();

        $totalExamen = DB::table('grupo_postulacion')
            ->distinct('postulacion_id')
            ->count('postulacion_id');

        $totalResultados = Postulacion::query()
            ->join('convocatorias as c', 'c.id', '=', 'postulaciones.convocatoria_id')
            ->whereNotNull('c.fecha_publicacion_resultados')
            ->where('c.fecha_publicacion_resultados', '<=', now())
            ->count();

        $funnel = [
            ['label' => 'Registro',    'value' => $totalRegistro],
            ['label' => 'Perfil',      'value' => $totalPerfil],
            ['label' => 'Postulación', 'value' => $totalPostul],
            ['label' => 'Pago',        'value' => $totalPago],
            ['label' => 'Examen',      'value' => $totalExamen],
            ['label' => 'Resultados',  'value' => $totalResultados],
        ];

        // ====== 3) Próximos eventos (exámenes/entrevistas) ======
        $preferFecha = ['programado_en','fecha_hora','fecha_inicio','fecha','inicio'];
        $fechaColUsada = null;
        foreach ($preferFecha as $col) {
            if (Schema::hasColumn('grupos_examen', $col)) {
                $fechaColUsada = $col;
                break;
            }
        }

        $candidatasLugar = ['lugar','salon','aula','sede','ubicacion'];
        $presentes = array_values(array_filter($candidatasLugar, fn($c) => Schema::hasColumn('grupos_examen', $c)));
        $placeExpr = empty($presentes)
            ? "''"
            : 'COALESCE('.implode(', ', array_map(fn($c) => "g.$c", $presentes)).", '')";

        $proximos = collect();
        if ($fechaColUsada) {
            $proximos = DB::table('grupos_examen as g')
                ->leftJoin('convocatorias as c', 'c.id', '=', 'g.convocatoria_id')
                ->whereNotNull("g.$fechaColUsada")
                ->where("g.$fechaColUsada", '>=', now()->subDay())
                ->orderBy("g.$fechaColUsada", 'asc')
                ->limit(10)
                ->get([
                    'g.id',
                    'g.tipo',
                    DB::raw("g.$fechaColUsada as fecha_evento"),
                    DB::raw("$placeExpr as lugar"),
                    DB::raw("COALESCE(c.titulo,'—') as convocatoria"),
                ])
                ->map(function ($r) {
                    $when = $r->fecha_evento instanceof \DateTimeInterface
                        ? Carbon::instance($r->fecha_evento)
                        : Carbon::parse($r->fecha_evento);
                    return (object)[
                        'id'           => $r->id,
                        'tipo'         => strtolower($r->tipo ?? 'actividad'),
                        'when'         => $when,
                        'lugar'        => $r->lugar ?: null,
                        'convocatoria' => $r->convocatoria,
                    ];
                });
        }

        // ====== 4) TOP INCIDENCIAS ======
        $EST_RECHAZADA = Postulacion::ESTATUS_RECHAZADA ?? 'rechazada';

        $pagosRechazados = Postulacion::query()
            ->with(['user:id,name,email', 'convocatoria:id,titulo'])
            ->where('estatus', $EST_RECHAZADA)
            ->latest('id')->limit(8)->get();

        $pagosPendientes = Postulacion::query()
            ->with(['user:id,name,email', 'convocatoria:id,titulo'])
            ->where('estatus', $EST_P_PEND)
            ->latest('id')->limit(8)->get();

        $duplicados = DB::table('postulaciones as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->join('convocatorias as c', 'c.id', '=', 'p.convocatoria_id')
            ->select(
                'p.user_id',
                'p.convocatoria_id',
                'u.name as user_name',
                'u.email as user_email',
                'c.titulo as convocatoria_titulo',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('p.user_id','p.convocatoria_id','u.name','u.email','c.titulo')
            ->having('total','>',1)
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ====== 5) DEMOGRÁFICOS (usar 'sexo' en vez de 'genero') ======
        // a) Distribución por sexo (normalizada) -> dejamos la variable como demGenero por compat con la vista
        $demGenero = DB::table('perfil_postulantes')
            ->selectRaw("
                CASE
                    WHEN LOWER(COALESCE(sexo,'')) IN ('masculino','m') THEN 'masculino'
                    WHEN LOWER(COALESCE(sexo,'')) IN ('femenino','f')  THEN 'femenino'
                    WHEN LOWER(COALESCE(sexo,'')) IN ('no_binario','nb') THEN 'no_binario'
                    WHEN LOWER(COALESCE(sexo,'')) IN ('prefiero_no_decir','ns/nc','na') THEN 'prefiero_no_decir'
                    ELSE 'no_especificado'
                END as genero_normalizado,
                COUNT(*) as total
            ")
            ->groupBy('genero_normalizado')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => (object)[ 'genero' => $r->genero_normalizado, 'total' => $r->total ]); // compat

        // b) Rangos de edad (derivados de fecha_nacimiento)
        $demEdad = DB::table('perfil_postulantes')
            ->selectRaw("
                SUM(CASE WHEN fecha_nacimiento IS NOT NULL AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 15 AND 17 THEN 1 ELSE 0 END) AS e_15_17,
                SUM(CASE WHEN fecha_nacimiento IS NOT NULL AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 20 THEN 1 ELSE 0 END) AS e_18_20,
                SUM(CASE WHEN fecha_nacimiento IS NOT NULL AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 21 AND 23 THEN 1 ELSE 0 END) AS e_21_23,
                SUM(CASE WHEN fecha_nacimiento IS NOT NULL AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) >= 24 THEN 1 ELSE 0 END) AS e_24_mas
            ")
            ->first();

        // c) Top prepas
        $demPrepas = DB::table('perfil_postulantes')
            ->select('preparatoria', DB::raw('COUNT(*) as total'))
            ->whereNotNull('preparatoria')
            ->groupBy('preparatoria')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ====== 6) Inconsistencias ======
        $pagadoSinFecha = Postulacion::query()
            ->with(['user:id,name,email', 'convocatoria:id,titulo'])
            ->where('estatus', $EST_PAGADO)->whereNull('fecha_pago')
            ->latest('id')->limit(8)->get();

        $pagadoSinReferencia = Postulacion::query()
            ->with(['user:id,name,email', 'convocatoria:id,titulo'])
            ->where('estatus', $EST_PAGADO)->whereNull('referencia_pago')
            ->latest('id')->limit(8)->get();

        $sinPerfil = DB::table('postulaciones as p')
            ->leftJoin('perfil_postulantes as pp', 'pp.user_id', '=', 'p.user_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->join('convocatorias as c', 'c.id', '=', 'p.convocatoria_id')
            ->whereNull('pp.user_id')
            ->select(
                'p.user_id',
                'u.name as user_name',
                'u.email as user_email',
                DB::raw('COUNT(*) as postulaciones'),
                DB::raw('MIN(c.titulo) as ejemplo_convocatoria')
            )
            ->groupBy('p.user_id','u.name','u.email')
            ->orderByDesc('postulaciones')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            // KPIs + embudo + próximos
            'totalPostulacionesActivas' => $totalPostulacionesActivas,
            'funnel'                    => $funnel,
            'proximos'                  => $proximos,
            'fechaColUsada'             => $fechaColUsada,

            // Incidencias
            'pagosRechazados'          => $pagosRechazados,
            'pagosPendientes'          => $pagosPendientes,
            'duplicados'               => $duplicados,
            'pagadoSinFecha'           => $pagadoSinFecha,
            'pagadoSinReferencia'      => $pagadoSinReferencia,
            'sinPerfil'                => $sinPerfil,

            // Demográficos (ya sin 'genero' en BD)
            'demGenero'                => $demGenero, // -> cada fila: { genero, total }
            'demEdad'                  => $demEdad,
            'demPrepas'                => $demPrepas,
        ]);
    }
}
