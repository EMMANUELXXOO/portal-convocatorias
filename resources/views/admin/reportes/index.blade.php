<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        // Para selects/filtros ligeros
        $convocatorias = DB::table('convocatorias')->select('id','titulo')->orderBy('titulo')->get();

        // Detectar columnas de perfil usadas frecuentemente (para mostrar nota)
        $perfilCols = DB::getSchemaBuilder()->getColumnListing('perfil_postulantes');

        return view('admin.reportes.index', compact('convocatorias','perfilCols'));
    }

    /* ========== CSV ========== */

    public function exportPostulacionesCsv(Request $request)
    {
        $q = DB::table('postulaciones as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('perfil_postulantes as pp', 'pp.user_id', '=', 'p.user_id')
            ->leftJoin('convocatorias as c', 'c.id', '=', 'p.convocatoria_id')
            ->selectRaw('
                p.id,
                p.folio,
                p.referencia_pago,
                p.estatus,
                p.fecha_pago,
                p.created_at as creada_en,
                p.ip_registro,
                u.name as usuario,
                u.email as correo_usuario,
                pp.nombre_completo,
                pp.telefono,
                pp.correo_contacto,
                c.id as convocatoria_id,
                c.titulo as convocatoria
            ')
            ->orderByDesc('p.id');

        if ($request->filled('convocatoria_id')) {
            $q->where('p.convocatoria_id', (int)$request->convocatoria_id);
        }
        if ($request->filled('estatus')) {
            $q->where('p.estatus', $request->estatus);
        }
        if ($s = trim((string)$request->get('buscar',''))) {
            $q->where(function($qq) use ($s){
                $qq->where('p.folio','like',"%$s%")
                   ->orWhere('p.referencia_pago','like',"%$s%")
                   ->orWhere('u.name','like',"%$s%")
                   ->orWhere('u.email','like',"%$s%")
                   ->orWhere('c.titulo','like',"%$s%");
            });
        }

        $filename = 'postulaciones_'.now()->format('Ymd_His').'.csv';
        $headers  = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM

            fputcsv($out, [
                'ID','Convocatoria ID','Convocatoria',
                'Folio','Referencia','Estatus','Fecha pago','Creada en','IP',
                'Usuario','Correo usuario','Nombre (perfil)','Teléfono (perfil)','Correo (perfil)'
            ]);

            $q->chunk(1000, function($rows) use ($out){
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id, $r->convocatoria_id, $r->convocatoria,
                        $r->folio, $r->referencia_pago, $r->estatus,
                        optional($r->fecha_pago)->format('Y-m-d H:i'),
                        optional($r->creada_en)->format('Y-m-d H:i'),
                        $r->ip_registro,
                        $r->usuario, $r->correo_usuario,
                        $r->nombre_completo, $r->telefono, $r->correo_contacto,
                    ]);
                }
            });

            fclose($out);
        }, 200, $headers);
    }

    public function exportPagosCsv(Request $request)
    {
        $q = DB::table('postulaciones as p')
            ->join('convocatorias as c','c.id','=','p.convocatoria_id')
            ->join('users as u','u.id','=','p.user_id')
            ->leftJoin('perfil_postulantes as pp','pp.user_id','=','p.user_id')
            ->where('p.estatus','pagado') // ajusta si usas constante
            ->selectRaw('
                p.id, p.folio, p.referencia_pago, p.folio_banco,
                p.fecha_pago, p.created_at as creada_en,
                u.name as usuario, u.email as correo_usuario,
                pp.nombre_completo, pp.correo_contacto, pp.telefono,
                c.id as convocatoria_id, c.titulo as convocatoria
            ')
            ->orderByDesc('p.fecha_pago');

        if ($request->filled('convocatoria_id')) {
            $q->where('p.convocatoria_id', (int)$request->convocatoria_id);
        }
        if ($request->filled('desde')) {
            $q->where('p.fecha_pago','>=', $request->desde.' 00:00:00');
        }
        if ($request->filled('hasta')) {
            $q->where('p.fecha_pago','<=', $request->hasta.' 23:59:59');
        }

        $filename = 'pagos_'.now()->format('Ymd_His').'.csv';
        $headers  = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($q) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'ID','Convocatoria ID','Convocatoria','Folio','Referencia',
                'Folio banco','Fecha pago','Creada en','Usuario','Correo usuario',
                'Nombre (perfil)','Correo (perfil)','Teléfono (perfil)'
            ]);
            $q->chunk(1000, function($rows) use ($out){
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id, $r->convocatoria_id, $r->convocatoria,
                        $r->folio, $r->referencia_pago, $r->folio_banco,
                        optional($r->fecha_pago)->format('Y-m-d H:i'),
                        optional($r->creada_en)->format('Y-m-d H:i'),
                        $r->usuario, $r->correo_usuario,
                        $r->nombre_completo, $r->correo_contacto, $r->telefono,
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }

    public function exportAsistenciaCsv(Request $request)
    {
        // Requiere ?grupo_id=#
        $grupoId = (int)$request->get('grupo_id');
        if (!$grupoId) {
            return back()->withErrors('Falta parámetro grupo_id.');
        }

        $q = DB::table('grupo_postulacion as gp')
            ->join('grupos_examen as g','g.id','=','gp.grupo_examen_id')
            ->join('postulaciones as p','p.id','=','gp.postulacion_id')
            ->join('users as u','u.id','=','p.user_id')
            ->leftJoin('perfil_postulantes as pp','pp.user_id','=','p.user_id')
            ->leftJoin('convocatorias as c','c.id','=','p.convocatoria_id')
            ->where('gp.grupo_examen_id', $grupoId)
            ->selectRaw('
                gp.postulacion_id,
                u.name as usuario, u.email as correo_usuario,
                pp.nombre_completo, pp.telefono, pp.correo_contacto,
                p.folio, p.referencia_pago, p.estatus,
                g.tipo, g.tipo_detalle, g.fecha_hora, g.lugar,
                c.titulo as convocatoria
            ')
            ->orderBy('u.name');

        $filename = "asistencia_grupo_{$grupoId}_".now()->format('Ymd_His').'.csv';
        $headers  = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($q){
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Postulación ID','Usuario','Correo usuario','Nombre (perfil)','Correo (perfil)','Teléfono (perfil)',
                'Folio','Referencia','Estatus','Tipo','Detalle','Fecha/Hora','Lugar','Convocatoria'
            ]);
            $q->chunk(1000, function($rows) use ($out){
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->postulacion_id, $r->usuario, $r->correo_usuario,
                        $r->nombre_completo, $r->correo_contacto, $r->telefono,
                        $r->folio, $r->referencia_pago, $r->estatus,
                        $r->tipo, $r->tipo_detalle,
                        optional($r->fecha_hora)->format('Y-m-d H:i'), $r->lugar, $r->convocatoria,
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }

    public function exportResultadosCsv(Request $request)
    {
        // Este reporte es tolerante: si no existe la tabla, devuelve encabezados vacíos
        if (!Schema::hasTable('resultados_examen')) {
            $filename = 'resultados_'.now()->format('Ymd_His').'.csv';
            return response()->stream(function(){
                $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['Postulación ID','Usuario','Correo','Convocatoria','Tipo','Puntaje','Estatus']);
                fclose($out);
            }, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }

        $q = DB::table('resultados_examen as r')
            ->join('postulaciones as p','p.id','=','r.postulacion_id')
            ->join('users as u','u.id','=','p.user_id')
            ->leftJoin('convocatorias as c','c.id','=','p.convocatoria_id')
            ->leftJoin('grupos_examen as g','g.id','=','r.grupo_examen_id')
            ->selectRaw('
                r.postulacion_id, u.name as usuario, u.email,
                c.titulo as convocatoria, g.tipo, r.puntaje, r.estatus as estatus_resultado
            ')
            ->orderByDesc('r.puntaje');

        if ($request->filled('convocatoria_id')) {
            $q->where('p.convocatoria_id', (int)$request->convocatoria_id);
        }

        $filename = 'resultados_'.now()->format('Ymd_His').'.csv';
        $headers  = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($q){
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Postulación ID','Usuario','Correo','Convocatoria','Tipo','Puntaje','Estatus']);
            $q->chunk(1000, function($rows) use ($out){
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->postulacion_id, $r->usuario, $r->email,
                        $r->convocatoria, $r->tipo, $r->puntaje, $r->estatus_resultado,
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }

    /* ========== PDF (opcionales, ejemplo simple) ========== */

    public function exportPostulacionesPdf(Request $request)
    {
        $rows = DB::table('postulaciones as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('convocatorias as c', 'c.id', '=', 'p.convocatoria_id')
            ->select('p.id','p.folio','p.estatus','p.fecha_pago','u.name as usuario','u.email','c.titulo as convocatoria')
            ->orderByDesc('p.id')
            ->limit(300) // limita para PDF
            ->get();

        $pdf = Pdf::loadView('admin.reportes.postulaciones_pdf', compact('rows'))
            ->setPaper('a4','portrait');

        return $pdf->download('postulaciones_'.now()->format('Ymd_His').'.pdf');
    }

    public function exportPagosPdf(Request $request)
    {
        $rows = DB::table('postulaciones as p')
            ->join('users as u','u.id','=','p.user_id')
            ->join('convocatorias as c','c.id','=','p.convocatoria_id')
            ->where('p.estatus','pagado')
            ->select('p.id','p.folio','p.referencia_pago','p.folio_banco','p.fecha_pago','u.name as usuario','c.titulo as convocatoria')
            ->orderByDesc('p.fecha_pago')
            ->limit(300)
            ->get();

        $pdf = Pdf::loadView('admin.reportes.pagos_pdf', compact('rows'))
            ->setPaper('a4','portrait');

        return $pdf->download('pagos_'.now()->format('Ymd_His').'.pdf');
    }
}
