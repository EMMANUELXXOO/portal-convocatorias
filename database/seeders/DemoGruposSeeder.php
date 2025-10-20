<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Convocatoria;
use App\Models\Postulacion;
use App\Models\GrupoExamen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class DemoGruposSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Admin: actualizar si existe, crear si no
        $admin = User::updateOrCreate(
            ['email' => 'informatica@escuelacruzrojatijuana.org'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('root195091'),
                'is_admin' => true,
                'role'     => 'admin',
            ]
        );

        // 2) Convocatoria demo (toma la primera existente o crea una)
        $conv = Convocatoria::first();
        if (! $conv) {
            $conv = Convocatoria::create([
                'titulo'       => 'Convocatoria Demo',
                'descripcion'  => 'Datos de prueba',
                'fecha_inicio' => now()->subDays(7),
                'fecha_fin'    => now()->addDays(30),
                // agrega aquí otros campos NOT NULL si tu esquema los exige
            ]);
        }

        // 3) Crear/actualizar 8 usuarios y su postulación (sin duplicar)
        for ($i = 1; $i <= 8; $i++) {
            $u = User::updateOrCreate(
                ['email' => "aspirante{$i}@example.com"],
                ['name' => "Aspirante {$i}", 'password' => Hash::make('password')]
            );

            // Genera folio único solo si vamos a crear la postulación
            $folio = $this->uniqueFolio();

            // Evita violar el índice único (user_id, convocatoria_id)
            $p = Postulacion::firstOrCreate(
                ['user_id' => $u->id, 'convocatoria_id' => $conv->id],
                [
                    'estatus'         => 'pagado',
                    'folio'           => $folio,
                    'referencia_pago' => 'REF-' . Str::upper(Str::random(8)),
                    'fecha_pago'      => now()->subDays(rand(1, 5)),
                    'ip_registro'     => '127.0.0.1',
                    'agente'          => 'Seeder',
                ]
            );

            // Si ya existía, aseguremos un estado coherente para la demo
            if ($p->wasRecentlyCreated === false) {
                $p->update([
                    'estatus'         => $p->estatus ?? 'pagado',
                    'fecha_pago'      => $p->fecha_pago ?: now()->subDays(1),
                ]);
            }
        }

        // 4) Grupos (idempotentes)
        $psico = GrupoExamen::updateOrCreate(
            [
                'convocatoria_id' => $conv->id,
                'tipo'            => 'psicometrico',
                'fecha_hora'      => Carbon::now()->addDays(3)->setTime(9, 0),
            ],
            [
                'lugar'           => 'Aula 101',
                'capacidad'       => 5,
            ]
        );

        $conoc = GrupoExamen::updateOrCreate(
            [
                'convocatoria_id' => $conv->id,
                'tipo'            => 'conocimiento',
                'fecha_hora'      => Carbon::now()->addDays(5)->setTime(10, 0),
            ],
            [
                'lugar'           => 'Aula 202',
                'capacidad'       => 5,
            ]
        );

        // 5) Asignar personas a grupos sin duplicar en el pivote
        $postIds = Postulacion::where('convocatoria_id', $conv->id)->pluck('id')->shuffle();

        // Para psico: toma hasta 3 que NO tengan ya psico
        $psicoCandidatos = Postulacion::whereIn('id', $postIds)
            ->whereDoesntHave('gruposExamen', fn($q) => $q->where('tipo', 'psicometrico'))
            ->limit(3)->pluck('id');

        $psico->postulaciones()->syncWithoutDetaching(
            $psicoCandidatos->mapWithKeys(fn($id) => [$id => ['asignado_en' => now()]])->all()
        );

        // Para conocimiento: toma 2 diferentes que NO tengan ya conocimiento
        $conocCandidatos = Postulacion::whereIn('id', $postIds->diff($psicoCandidatos))
            ->whereDoesntHave('gruposExamen', fn($q) => $q->where('tipo', 'conocimiento'))
            ->limit(2)->pluck('id');

        $conoc->postulaciones()->syncWithoutDetaching(
            $conocCandidatos->mapWithKeys(fn($id) => [$id => ['asignado_en' => now()]])->all()
        );
    }

    private function uniqueFolio(): string
    {
        // Genera un folio único (por si tienes unique en la columna)
        do {
            $folio = 'FOLIO-' . Str::upper(Str::random(6));
        } while (Postulacion::where('folio', $folio)->exists());

        return $folio;
    }
}
