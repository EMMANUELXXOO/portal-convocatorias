<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('grupo_postulacion')) {
            // Crear desde cero
            Schema::create('grupo_postulacion', function (Blueprint $table) {
                $table->id();

                $table->foreignId('grupo_examen_id')
                      ->constrained('grupos_examen')
                      ->cascadeOnDelete();

                $table->foreignId('postulacion_id')
                      ->constrained('postulaciones')
                      ->cascadeOnDelete();

                // si quieres que se setee automáticamente al asignar
                $table->timestamp('asignado_en')->nullable()->useCurrent();

                // índices
                $table->unique(['grupo_examen_id','postulacion_id'], 'ux_grupo_postulacion');
                $table->index('postulacion_id', 'ix_gp_postulacion');
                $table->index('grupo_examen_id', 'ix_gp_grupo');
            });

            return;
        }

        // --- Ya existe: solo aseguramos columna/índices que puedan faltar ---
        Schema::table('grupo_postulacion', function (Blueprint $table) {
            // Columna asignado_en si no existe
            if (! Schema::hasColumn('grupo_postulacion', 'asignado_en')) {
                $table->timestamp('asignado_en')->nullable()->useCurrent();
            }

            // Índice único (evita duplicados del mismo postulante en el mismo grupo)
            $this->ensureUnique($table, 'ux_grupo_postulacion', ['grupo_examen_id','postulacion_id']);

            // Índices simples
            $this->ensureIndex($table, 'ix_gp_postulacion', ['postulacion_id']);
            $this->ensureIndex($table, 'ix_gp_grupo', ['grupo_examen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_postulacion');
    }

    // Helpers para no romper si el índice ya existe
    private function ensureUnique(Blueprint $table, string $name, array $columns): void
    {
        // Laravel no tiene Schema::hasIndex nativo; si el nombre no existe, intentará crearlo.
        // Si tu DB ya lo tiene con otro nombre, comenta esta línea o ajusta el nombre.
        try { $table->unique($columns, $name); } catch (\Throwable $e) {}
    }

    private function ensureIndex(Blueprint $table, string $name, array $columns): void
    {
        try { $table->index($columns, $name); } catch (\Throwable $e) {}
    }
};
