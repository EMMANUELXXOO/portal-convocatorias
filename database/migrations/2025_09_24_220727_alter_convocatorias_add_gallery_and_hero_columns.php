<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // Portada (por si no existe)
            if (!Schema::hasColumn('convocatorias', 'portada_path')) {
                $table->string('portada_path')->nullable()->after('cupo_total');
            }

            // Galería como JSON
            if (!Schema::hasColumn('convocatorias', 'galeria_urls')) {
                $table->json('galeria_urls')->nullable()->after('fecha_curso_propedeutico_fin');
            }

            // Carrusel habilitado
            if (!Schema::hasColumn('convocatorias', 'carousel_enabled')) {
                $table->boolean('carousel_enabled')->default(true)->after('galeria_urls');
            }

            // Ajuste de imagen
            if (!Schema::hasColumn('convocatorias', 'hero_fit')) {
                // enum simple
                $table->enum('hero_fit', ['cover','contain'])->default('cover')->after('carousel_enabled');
            }

            // Notas (por si no existiera)
            if (!Schema::hasColumn('convocatorias', 'notas')) {
                $table->text('notas')->nullable()->after('hero_fit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // Quitar solo lo que agregamos
            if (Schema::hasColumn('convocatorias', 'hero_fit')) {
                $table->dropColumn('hero_fit');
            }
            if (Schema::hasColumn('convocatorias', 'carousel_enabled')) {
                $table->dropColumn('carousel_enabled');
            }
            if (Schema::hasColumn('convocatorias', 'galeria_urls')) {
                $table->dropColumn('galeria_urls');
            }
            // Estas dos quizá ya existían antes; bórralas solo si las agregaste aquí:
            // if (Schema::hasColumn('convocatorias', 'portada_path')) $table->dropColumn('portada_path');
            // if (Schema::hasColumn('convocatorias', 'notas')) $table->dropColumn('notas');
        });
    }
};
