<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Convocatoria;

class ConvocatoriaController extends Controller
{
    /**
     * GET /convocatorias
     * Lista solo las visibles (estatus=activa y no han terminado).
     */
    public function index(Request $request)
    {
        // Hoy como fecha (evita TZ problems)
        $today = now()->toDateString();

        $convocatorias = Convocatoria::query()
            // visibles = activa y fecha_fin >= hoy (o null)
            ->where('estatus', 'activa')
            ->where(fn($q) => $q->whereNull('fecha_fin')
                                ->orWhereDate('fecha_fin', '>=', $today))
            // orden: primero por fecha_inicio (nulos al final), luego id desc
            ->orderByRaw('ISNULL(fecha_inicio), fecha_inicio ASC')
            ->orderByDesc('id')
            ->withCount('postulaciones')
            ->paginate(12)
            ->withQueryString();

        return view('convocatorias.index', compact('convocatorias'));
    }

    /**
     * GET /convocatorias/{convocatoria}
     */
    public function show(Request $request, Convocatoria $convocatoria)
    {
        $convocatoria->loadMissing([
            // 'requisitos', 'documentos',
        ]);

        $postulacionActual = null;
        $inspeccion = null;

        if ($request->user()) {
            $postulacionActual = $request->user()
                ->postulaciones()
                ->where('convocatoria_id', $convocatoria->id)
                ->latest('id')
                ->first();

            $inspeccion = Gate::forUser($request->user())->inspect('postular', $convocatoria);
        }

        return view('convocatorias.show', [
            'convocatoria'      => $convocatoria,
            'postulacionActual' => $postulacionActual,
            'inspeccion'        => $inspeccion,
        ]);
    }
}
