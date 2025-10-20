<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // helper: ¿existe índice con ese nombre?
    private function indexExists(string $table, string $index): bool
    {
        $row = DB::selectOne("
            SELECT COUNT(1) AS c
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ", [$table, $index]);

        return isset($row->c) && (int)$row->c > 0;
    }

    // helper: dropUnique solo si existe
    private function safeDropUnique(Blueprint $table, string $tableName, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            $table->dropUnique($indexName);
        }
    }

    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('postulaciones', 'folio')) {
                $table->string('folio', 32)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('postulaciones', 'referencia_pago')) {
                $table->string('referencia_pago', 64)->nullable()->after('folio');
            }
            if (!Schema::hasColumn('postulaciones', 'folio_banco')) {
                $table->string('folio_banco', 64)->nullable()->unique()->after('referencia_pago');
            }
            if (!Schema::hasColumn('postulaciones', 'estatus')) {
                $table->string('estatus', 24)->default('pendiente')->after('folio_banco');
            }
            if (!Schema::hasColumn('postulaciones', 'fecha_pago')) {
                $table->timestamp('fecha_pago')->nullable()->after('estatus');
            }
            if (!Schema::hasColumn('postulaciones', 'recibo_reenvios')) {
                $table->unsignedTinyInteger('recibo_reenvios')->default(0)->after('fecha_pago');
            }
            if (!Schema::hasColumn('postulaciones', 'recibo_enviado_at')) {
                $table->timestamp('recibo_enviado_at')->nullable()->after('recibo_reenvios');
            }
            if (!Schema::hasColumn('postulaciones', 'ip_registro')) {
                $table->string('ip_registro', 45)->nullable()->after('recibo_enviado_at');
            }
            if (!Schema::hasColumn('postulaciones', 'agente')) {
                $table->string('agente', 255)->nullable()->after('ip_registro');
            }
            if (!Schema::hasColumn('postulaciones', 'user_edit_count')) {
                $table->unsignedTinyInteger('user_edit_count')->default(0)->after('agente');
            }
            if (!Schema::hasColumn('postulaciones', 'user_first_edit_at')) {
                $table->timestamp('user_first_edit_at')->nullable()->after('user_edit_count');
            }
        });

        // Índice compuesto único (solo si no existe)
        $compoundIndex = 'postulaciones_user_id_convocatoria_id_unique';
        if (! $this->indexExists('postulaciones', $compoundIndex)) {
            Schema::table('postulaciones', function (Blueprint $table) use ($compoundIndex) {
                $table->unique(['user_id', 'convocatoria_id'], $compoundIndex);
            });
        }
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Quitar índices únicos de forma segura
            // Nota: los nombres que crea Laravel por defecto:
            // - postulaciones_user_id_convocatoria_id_unique
            // - postulaciones_folio_unique
            // - postulaciones_folio_banco_unique
        });

        // Tenemos que reabrir el Schema::table para usar helpers (no hay $this dentro del closure si no lo capturamos):
        $self = $this;

        Schema::table('postulaciones', function (Blueprint $table) use ($self) {
            $self->safeDropUnique($table, 'postulaciones', 'postulaciones_user_id_convocatoria_id_unique');
            $self->safeDropUnique($table, 'postulaciones', 'postulaciones_folio_unique');
            $self->safeDropUnique($table, 'postulaciones', 'postulaciones_folio_banco_unique');

            // Quitar columnas solo si existen
            $cols = [
                'folio','referencia_pago','folio_banco','estatus','fecha_pago',
                'recibo_reenvios','recibo_enviado_at','ip_registro','agente',
                'user_edit_count','user_first_edit_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('postulaciones', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
