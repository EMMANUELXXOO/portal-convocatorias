<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // Costos
            if (!Schema::hasColumn('convocatorias', 'precio_ficha')) {
                $table->decimal('precio_ficha', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'precio_inscripcion')) {
                $table->decimal('precio_inscripcion', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'precio_mensualidad')) {
                $table->decimal('precio_mensualidad', 10, 2)->nullable();
            }

            // Cupo
            if (!Schema::hasColumn('convocatorias', 'cupo_total')) {
                $table->unsignedInteger('cupo_total')->nullable();
            }

            // Portada / contacto / ubicación
            if (!Schema::hasColumn('convocatorias', 'portada_path')) {
                $table->string('portada_path', 255)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'ubicacion')) {
                $table->string('ubicacion', 255)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'telefono_1')) {
                $table->string('telefono_1', 50)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'telefono_2')) {
                $table->string('telefono_2', 50)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'correo_1')) {
                $table->string('correo_1', 150)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'correo_2')) {
                $table->string('correo_2', 150)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'horario_atencion')) {
                $table->string('horario_atencion', 150)->nullable();
            }

            // Programa / requisitos / documentos
            if (!Schema::hasColumn('convocatorias', 'requisitos_examen_entrevista')) {
                $table->text('requisitos_examen_entrevista')->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'requisitos_generales')) {
                $table->text('requisitos_generales')->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'documentos_requeridos')) {
                $table->text('documentos_requeridos')->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'certificaciones_adicionales')) {
                $table->string('certificaciones_adicionales', 255)->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'duracion')) {
                $table->string('duracion', 100)->nullable();
            }

            // Fechas clave
            if (!Schema::hasColumn('convocatorias', 'fecha_publicacion_resultados')) {
                $table->date('fecha_publicacion_resultados')->nullable();
            }
            if (!Schema::hasColumn('convocatorias', 'fecha_inicio_clases')) {
                $table->date('fecha_inicio_clases')->nullable();
            }

            // Notas
            if (!Schema::hasColumn('convocatorias', 'notas')) {
                $table->text('notas')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            $cols = [
                'precio_ficha','precio_inscripcion','precio_mensualidad',
                'cupo_total',
                'portada_path','ubicacion','telefono_1','telefono_2',
                'correo_1','correo_2','horario_atencion',
                'requisitos_examen_entrevista','requisitos_generales','documentos_requeridos',
                'certificaciones_adicionales','duracion',
                'fecha_publicacion_resultados','fecha_inicio_clases',
                'notas',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('convocatorias', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
