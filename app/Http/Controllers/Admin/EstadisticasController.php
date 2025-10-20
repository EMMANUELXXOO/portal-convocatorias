<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EstadisticasController extends Controller
{
    public function demograficas(Request $request)
    {
        $tabla = 'perfil_postulantes';

        // Detectar columnas disponibles
        $colGenero = $this->firstExisting($tabla, ['genero','sexo']);
        $colFechaN = $this->firstExisting($tabla, ['fecha_nac','fecha_nacimiento']);
        $colPrepa  = $this->firstExisting($tabla, ['preparatoria','escuela_procedencia','prepa','bachillerato']);

        // ===== Género / Sexo =====
        $generoData = collect();
        if ($colGenero) {
            $generoData = DB::table($tabla)
                ->select(DB::raw("$colGenero as label"), DB::raw('COUNT(*) as total'))
                ->groupBy($colGenero)
                ->orderByDesc('total')
                ->get()
                ->map(function($r){
                    $label = trim((string)$r->label) ?: 'No especifica';
                    return (object)['label'=>$label, 'total'=>(int)$r->total];
                });
        }

        // ===== Edades (a partir de fecha de nacimiento) =====
        $ageBuckets = collect([
            '≤17' => 0, '18–20' => 0, '21–25' => 0, '26–30' => 0, '31–35' => 0, '36+' => 0, 'Sin dato' => 0,
        ]);

        if ($colFechaN) {
            DB::table($tabla)->select($colFechaN)->orderByDesc($colFechaN)->chunk(2000, function($chunk) use (&$ageBuckets, $colFechaN) {
                foreach ($chunk as $row) {
                    $d = $row->{$colFechaN};
                    if (!$d) { $ageBuckets['Sin dato']++; continue; }
                    try {
                        $age = Carbon::parse($d)->age;
                    } catch (\Throwable $e) {
                        $ageBuckets['Sin dato']++; continue;
                    }
                    if ($age <= 17)         $ageBuckets['≤17']++;
                    elseif ($age <= 20)     $ageBuckets['18–20']++;
                    elseif ($age <= 25)     $ageBuckets['21–25']++;
                    elseif ($age <= 30)     $ageBuckets['26–30']++;
                    elseif ($age <= 35)     $ageBuckets['31–35']++;
                    else                    $ageBuckets['36+']++;
                }
            });
        }

        // ===== Preparatorias (Top 10) =====
        $prepas = collect();
        if ($colPrepa) {
            $prepas = DB::table($tabla)
                ->select(DB::raw("$colPrepa as label"), DB::raw('COUNT(*) as total'))
                ->groupBy($colPrepa)
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function($r){
                    $label = trim((string)$r->label) ?: 'No especifica';
                    return (object)['label'=>$label, 'total'=>(int)$r->total];
                });
        }

        return view('admin.estadisticas.demograficas', [
            'colGenero'  => $colGenero,
            'colFechaN'  => $colFechaN,
            'colPrepa'   => $colPrepa,
            'generoData' => $generoData,
            'ageBuckets' => $ageBuckets,
            'prepas'     => $prepas,
        ]);
    }

    private function firstExisting(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }
}
