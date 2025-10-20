<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // Crea la portada si no existe
            if (!Schema::hasColumn('convocatorias', 'portada_path')) {
                $table->string('portada_path')->nullable()->after('cupo_total');
            }

            // Crea galeria_urls si no existe (JSON)
            if (!Schema::hasColumn('convocatorias', 'galeria_urls')) {
                $table->json('galeria_urls')->nullable()->after('fecha_curso_propedeutico_fin');
            } else {
                // Si existe pero es texto, intenta convertir a JSON (MySQL soporta modify en 5.7+)
                // Omitir si tu motor no lo permite.
                try {
                    $table->json('galeria_urls')->nullable()->change();
                } catch (\Throwable $e) {
                    // En entornos donde no se puede change(), al menos asegúrate que el modelo castee a array.
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // Revertir solo si lo necesitas; normalmente no se borran columnas en down()
            // $table->dropColumn('portada_path');
            // $table->dropColumn('galeria_urls');
        });
    }
};
