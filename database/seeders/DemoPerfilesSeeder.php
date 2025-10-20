<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;

class DemoPerfilesSeeder extends Seeder
{
    public function run(): void
    {
        $faker   = Faker::create('es_MX');
        $generos = [null,'femenino','masculino','no_binario','prefiero_no_decir'];
        $prepas  = ['Prepa 1','Prepa 2','Colegio X','CBTis 21','Conalep A', null];

        $hasFechaNac  = Schema::hasColumn('perfil_postulantes', 'fecha_nac');
        $hasSexo      = Schema::hasColumn('perfil_postulantes', 'sexo');
        $hasPrepa     = Schema::hasColumn('perfil_postulantes', 'prepa');

        $userIds = DB::table('users')->select('id','name')->limit(200)->get();

        foreach ($userIds as $u) {
            if (!DB::table('perfil_postulantes')->where('user_id', $u->id)->exists()) {
                $fecha = now()->subYears(rand(17,28))->subDays(rand(0,365))->toDateString();
                $genero = $generos[array_rand($generos)];
                $prepa  = $prepas[array_rand($prepas)];

                $data = [
                    'user_id'          => $u->id,
                    'nombre_completo'  => $u->name ?: $faker->name,
                    'genero'           => $genero,
                    'fecha_nacimiento' => $fecha,
                    'preparatoria'     => $prepa,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                // Campos legacy si existen y son NOT NULL
                if ($hasFechaNac)  $data['fecha_nac'] = $fecha;       // espejo de fecha_nacimiento
                if ($hasSexo)      $data['sexo']      = $genero;      // espejo de genero
                if ($hasPrepa)     $data['prepa']     = $prepa;       // espejo de preparatoria

                DB::table('perfil_postulantes')->insert($data);
            }
        }
    }
}
