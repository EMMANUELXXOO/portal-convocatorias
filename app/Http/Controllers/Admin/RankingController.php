<?php
// app/Http/Controllers/Admin/RankingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $convocatoriaId = $request->integer('convocatoria_id');

        $q = DB::table('resultados_examen as r')
            ->join('postulaciones as p','p.id','=','r.postulacion_id')
            ->join('users as u','u.id','=','p.user_id')
            ->join('convocatorias as c','c.id','=','p.convocatoria_id')
            ->when($convocatoriaId, fn($qq)=>$qq->where('p.convocatoria_id',$convocatoriaId))
            ->select('u.name','u.email','c.titulo as convocatoria','r.tipo','r.puntaje_total','r.created_at')
            ->orderByDesc('r.puntaje_total');

        $items = $q->paginate(20)->withQueryString();

        return view('admin.ranking.index', compact('items','convocatoriaId'));
    }
}
