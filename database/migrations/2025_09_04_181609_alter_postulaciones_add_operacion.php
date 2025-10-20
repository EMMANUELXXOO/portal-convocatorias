<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('postulaciones', 'folio')) {
                $table->string('folio', 32)->unique()->after('estatus');
            }
            if (!Schema::hasColumn('postulaciones', 'referencia_pago')) {
                $table->string('referencia_pago', 64)->nullable()->after('folio');
            }
            if (!Schema::hasColumn('postulaciones', 'fecha_pago')) {
                $table->timestamp('fecha_pago')->nullable()->after('referencia_pago');
            }
            if (!Schema::hasColumn('postulaciones', 'ip_registro')) {
                $table->ipAddress('ip_registro')->nullable()->after('fecha_pago');
            }
            if (!Schema::hasColumn('postulaciones', 'agente')) {
                $table->string('agente', 255)->nullable()->after('ip_registro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            if (Schema::hasColumn('postulaciones','agente')) $table->dropColumn('agente');
            if (Schema::hasColumn('postulaciones','ip_registro')) $table->dropColumn('ip_registro');
            if (Schema::hasColumn('postulaciones','fecha_pago')) $table->dropColumn('fecha_pago');
            if (Schema::hasColumn('postulaciones','referencia_pago')) $table->dropColumn('referencia_pago');
            if (Schema::hasColumn('postulaciones','folio')) {
                $table->dropUnique(['folio']); // MySQL: nombre automático
                $table->dropColumn('folio');
            }
        });
    }
};
