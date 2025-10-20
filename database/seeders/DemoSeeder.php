<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Convocatoria;
use App\Models\Postulacion;
use App\Models\GrupoExamen;

class DemoGruposSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Convocatoria
        $conv = Convocatoria::first() ?? Convocatoria::factory()->create([
            'titulo' => 'Convocatoria Demo',
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin'    => now()->addDays(30),
        ]);

        // 2) Usuarios + Postulaciones
        $usuarios = User::factory()->count(8)->create();
        foreach ($usuarios as $u) {
            Postulacion::factory()->create([
                'user_id'         => $u->id,
                'convocatoria_id' => $conv->id,
                'estatus'         => 'pagado', // para que sea verosímil
                'fecha_pago'      => now()->subDays(rand(1,5)),
            ]);
        }

        // 3) Grupos
        $psico = GrupoExamen::create([
            'convocatoria_id' => $conv->id,
            'tipo'            => 'psicometrico',
            'fecha_hora'      => now()->addDays(3)->setTime(9, 0),
            'lugar'           => 'Aula 101',
            'capacidad'       => 5,
        ]);

        $conoc = GrupoExamen::create([
            'convocatoria_id' => $conv->id,
            'tipo'            => 'conocimiento',
            'fecha_hora'      => now()->addDays(5)->setTime(10, 0),
            'lugar'           => 'Aula 202',
            'capacidad'       => 5,
        ]);

        // 4) Asignar 3 y 2 aleatorios para que veas el resumen en tarjetas
        $postIds = Postulacion::where('convocatoria_id',$conv->id)->pluck('id')->shuffle();
        $psico->postulaciones()->attach($postIds->take(3)->all(), ['asignado_en' => now()]);
        $conoc->postulaciones()->attach($postIds->slice(3,2)->all(), ['asignado_en' => now()]);
    }
}
