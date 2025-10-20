<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // Estado y básicos
            if (!Schema::hasColumn('convocatorias', 'estatus'))                   $table->string('estatus', 20)->default('activa');
            if (!Schema::hasColumn('convocatorias', 'cupo_total'))                $table->unsignedInteger('cupo_total')->nullable();

            // Costos
            if (!Schema::hasColumn('convocatorias', 'precio_ficha'))              $table->decimal('precio_ficha', 10, 2)->nullable();
            if (!Schema::hasColumn('convocatorias', 'precio_inscripcion'))        $table->decimal('precio_inscripcion', 10, 2)->nullable();
            if (!Schema::hasColumn('convocatorias', 'precio_mensualidad'))        $table->decimal('precio_mensualidad', 10, 2)->nullable();

            // Portada / ubicación / contacto
            if (!Schema::hasColumn('convocatorias', 'portada_path'))              $table->string('portada_path')->nullable();
            if (!Schema::hasColumn('convocatorias', 'ubicacion'))                 $table->string('ubicacion')->nullable();
            if (!Schema::hasColumn('convocatorias', 'telefono_1'))                $table->string('telefono_1', 50)->nullable();
            if (!Schema::hasColumn('convocatorias', 'telefono_2'))                $table->string('telefono_2', 50)->nullable();
            if (!Schema::hasColumn('convocatorias', 'correo_1'))                  $table->string('correo_1', 120)->nullable();
            if (!Schema::hasColumn('convocatorias', 'correo_2'))                  $table->string('correo_2', 120)->nullable();
            if (!Schema::hasColumn('convocatorias', 'horario_atencion'))          $table->string('horario_atencion', 180)->nullable();

            // Programa y horarios
            if (!Schema::hasColumn('convocatorias', 'duracion'))                  $table->string('duracion', 120)->nullable();
            if (!Schema::hasColumn('convocatorias', 'certificaciones_adicionales')) $table->string('certificaciones_adicionales', 255)->nullable();
            if (!Schema::hasColumn('convocatorias', 'horario_matutino'))          $table->string('horario_matutino', 80)->nullable();
            if (!Schema::hasColumn('convocatorias', 'horario_vespertino'))        $table->string('horario_vespertino', 80)->nullable();

            // Requisitos / documentos
            if (!Schema::hasColumn('convocatorias', 'requisitos_generales'))      $table->text('requisitos_generales')->nullable();
            if (!Schema::hasColumn('convocatorias', 'requisitos_examen_entrevista')) $table->text('requisitos_examen_entrevista')->nullable();
            if (!Schema::hasColumn('convocatorias', 'documentos_requeridos'))     $table->text('documentos_requeridos')->nullable();

            // Fechas clave
            if (!Schema::hasColumn('convocatorias', 'fecha_publicacion_resultados')) $table->date('fecha_publicacion_resultados')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_inicio_clases'))       $table->date('fecha_inicio_clases')->nullable();

            // Proceso de admisión
            if (!Schema::hasColumn('convocatorias', 'fecha_entrega_solicitudes_inicio')) $table->date('fecha_entrega_solicitudes_inicio')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_entrega_solicitudes_fin'))    $table->date('fecha_entrega_solicitudes_fin')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_psicometrico_inicio'))  $table->date('fecha_psicometrico_inicio')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_psicometrico_fin'))     $table->date('fecha_psicometrico_fin')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_entrevistas_inicio'))   $table->date('fecha_entrevistas_inicio')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_entrevistas_fin'))      $table->date('fecha_entrevistas_fin')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_examen_conocimientos')) $table->date('fecha_examen_conocimientos')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_curso_propedeutico_inicio')) $table->date('fecha_curso_propedeutico_inicio')->nullable();
            if (!Schema::hasColumn('convocatorias', 'fecha_curso_propedeutico_fin'))    $table->date('fecha_curso_propedeutico_fin')->nullable();

            // Galería y notas
            if (!Schema::hasColumn('convocatorias', 'galeria_urls'))              $table->json('galeria_urls')->nullable();
            if (!Schema::hasColumn('convocatorias', 'notas'))                     $table->text('notas')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            $cols = [
                'estatus','cupo_total',
                'precio_ficha','precio_inscripcion','precio_mensualidad',
                'portada_path','ubicacion','telefono_1','telefono_2','correo_1','correo_2','horario_atencion',
                'duracion','certificaciones_adicionales','horario_matutino','horario_vespertino',
                'requisitos_generales','requisitos_examen_entrevista','documentos_requeridos',
                'fecha_publicacion_resultados','fecha_inicio_clases',
                'fecha_entrega_solicitudes_inicio','fecha_entrega_solicitudes_fin',
                'fecha_psicometrico_inicio','fecha_psicometrico_fin',
                'fecha_entrevistas_inicio','fecha_entrevistas_fin',
                'fecha_examen_conocimientos',
                'fecha_curso_propedeutico_inicio','fecha_curso_propedeutico_fin',
                'galeria_urls','notas',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('convocatorias', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
