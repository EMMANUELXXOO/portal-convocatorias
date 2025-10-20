<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Edición por usuario (una sola vez)
            if (!Schema::hasColumn('postulaciones', 'user_edit_count')) {
                $table->unsignedTinyInteger('user_edit_count')->default(0)->after('estatus');
            }
            if (!Schema::hasColumn('postulaciones', 'user_first_edit_at')) {
                $table->timestamp('user_first_edit_at')->nullable()->after('user_edit_count');
            }

            // Métricas de recibo
            if (!Schema::hasColumn('postulaciones', 'recibo_reenvios')) {
                $table->unsignedInteger('recibo_reenvios')->default(0)->after('folio_banco');
            }
            if (!Schema::hasColumn('postulaciones', 'recibo_enviado_at')) {
                $table->timestamp('recibo_enviado_at')->nullable()->after('recibo_reenvios');
            }
        });
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Drops seguros (solo si existen)
            if (Schema::hasColumn('postulaciones', 'user_edit_count')) {
                $table->dropColumn('user_edit_count');
            }
            if (Schema::hasColumn('postulaciones', 'user_first_edit_at')) {
                $table->dropColumn('user_first_edit_at');
            }
            if (Schema::hasColumn('postulaciones', 'recibo_reenvios')) {
                $table->dropColumn('recibo_reenvios');
            }
            if (Schema::hasColumn('postulaciones', 'recibo_enviado_at')) {
                $table->dropColumn('recibo_enviado_at');
            }
        });
    }
};
