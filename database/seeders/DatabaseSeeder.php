<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Llama a los seeders que quieras ejecutar por defecto
        $this->call([
            DemoGruposSeeder::class,   // <- lo creamos abajo
            // Agrega aquí otros seeders si los necesitas
        ]);
    }
}
