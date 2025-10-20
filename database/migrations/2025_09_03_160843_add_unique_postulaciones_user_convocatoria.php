<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Única por (user_id, convocatoria_id)
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'convocatoria_id'],
                'ux_postulaciones_user_convocatoria'
            );
        });

        // 2) Índice por (convocatoria_id, estatus) SOLO si no existe ya
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'postulaciones')
            ->where('index_name', 'postulaciones_convocatoria_id_estatus_index')
            ->exists();

        if (! $exists) {
            Schema::table('postulaciones', function (Blueprint $table) {
                $table->index(
                    ['convocatoria_id', 'estatus'],
                    'postulaciones_convocatoria_id_estatus_index'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Quita la única
            $table->dropUnique('ux_postulaciones_user_convocatoria');

            // Quita el índice (si existe)
            try {
                $table->dropIndex('postulaciones_convocatoria_id_estatus_index');
            } catch (\Throwable $e) {
                // no-op si no existe
            }
        });
    }
};
