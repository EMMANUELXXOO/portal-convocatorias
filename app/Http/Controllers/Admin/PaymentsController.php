<?php
// app/Http/Controllers/Admin/PaymentsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Postulacion;
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        // Filtros simples
        $q = Postulacion::query()
            ->with(['user:id,name,email','convocatoria:id,titulo'])
            ->when($request->filled('convocatoria_id'), fn($qq)=>$qq->where('convocatoria_id',$request->convocatoria_id))
            ->when($request->filled('buscar'), function($qq) use($request){
                $b = '%'.$request->buscar.'%';
                $qq->whereHas('user', fn($u)=>$u->where('name','like',$b)->orWhere('email','like',$b));
            })
            ->where('estatus', Postulacion::ESTATUS_PAGO_PENDIENTE ?? 'pago_pendiente')
            ->latest('id');

        $items = $q->paginate(20)->withQueryString();

        return view('admin.pagos.validar', compact('items'));
    }

    public function aprobarSeleccion(Request $request)
    {
        $ids = collect($request->input('ids', []))->filter()->map('intval')->all();
        if (empty($ids)) {
            return back()->with('error','Selecciona al menos un registro.');
        }

        $now = now();
        DB::transaction(function() use ($ids, $now) {
            Postulacion::whereIn('id',$ids)->update([
                'estatus' => Postulacion::ESTATUS_PAGADO ?? 'pagado',
                'fecha_pago' => $now,
            ]);
        });

        return back()->with('ok','Pagos aprobados: '.count($ids));
    }
}
