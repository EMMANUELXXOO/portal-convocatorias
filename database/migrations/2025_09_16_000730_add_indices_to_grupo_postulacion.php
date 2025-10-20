<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Comprueba por nombre si el índice existe en MySQL */
    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
        return !empty($rows);
    }

    public function up(): void
    {
        // Agrega SOLO lo que no exista aún
        Schema::table('grupo_postulacion', function (Blueprint $table) {
            // El closure queda vacío a propósito; haremos las llamadas condicionales debajo.
        });

        if (!$this->indexExists('grupo_postulacion', 'ux_grupo_postulacion')) {
            Schema::table('grupo_postulacion', function (Blueprint $table) {
                $table->unique(['grupo_examen_id','postulacion_id'], 'ux_grupo_postulacion');
            });
        }

        if (!$this->indexExists('grupo_postulacion', 'ix_gp_postulacion')) {
            Schema::table('grupo_postulacion', function (Blueprint $table) {
                $table->index('postulacion_id', 'ix_gp_postulacion');
            });
        }

        if (!$this->indexExists('grupo_postulacion', 'ix_gp_grupo')) {
            Schema::table('grupo_postulacion', function (Blueprint $table) {
                $table->index('grupo_examen_id', 'ix_gp_grupo');
            });
        }
    }

    public function down(): void
    {
        // Elimina SOLO si existen
        if ($this->indexExists('grupo_postulacion', 'ux_grupo_postulacion')) {
            Schema::table('grupo_postulacion', function (Blueprint $table) {
                $table->dropUnique('ux_grupo_postulacion');
            });
        }
        if ($this->indexExists('grupo_postulacion', 'ix_gp_postulacion')) {
            Schema::table('grupo_postulacion', function (Blueprint $table) {
                $table->dropIndex('ix_gp_postulacion');
            });
        }
        if ($this->indexExists('grupo_postulacion', 'ix_gp_grupo')) {
            Schema::table('grupo_postulacion', function (Blueprint $table) {
                $table->dropIndex('ix_gp_grupo');
            });
        }
    }
};
