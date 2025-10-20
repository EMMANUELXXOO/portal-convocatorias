<?php
// app/Http/Controllers/Admin/AnalyticsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function demografia(Request $request)
    {
        // Asumimos columnas en perfil_postulantes:
        // genero (M/F/otro), fecha_nacimiento (date), preparatoria_origen (string)
        // Puedes ajustar nombres si difieren.

        // Género
        $genero = DB::table('perfil_postulantes')
            ->select('genero', DB::raw('COUNT(*) as total'))
            ->groupBy('genero')->orderByDesc('total')->get();

        // Edades: bucket en rangos
        // TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())
        $edades = DB::table('perfil_postulantes')
            ->selectRaw("
                CASE
                  WHEN fecha_nacimiento IS NULL THEN 'Sin dato'
                  WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 18 THEN '<18'
                  WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 20 THEN '18-20'
                  WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 21 AND 25 THEN '21-25'
                  WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 26 AND 30 THEN '26-30'
                  ELSE '31+'
                END as rango,
                COUNT(*) as total
            ")
            ->groupBy('rango')->orderBy('rango')->get();

        // Top preparatorias
        $prepas = DB::table('perfil_postulantes')
            ->select('preparatoria_origen as prepa', DB::raw('COUNT(*) as total'))
            ->groupBy('prepa')
            ->orderByDesc('total')
            ->limit(10)->get();

        return view('admin.analytics.demografia', compact('genero','edades','prepas'));
    }
}
