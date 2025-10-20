<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grupos_examen', function (Blueprint $table) {
            if (! Schema::hasColumn('grupos_examen', 'tipo_detalle')) {
                $table->string('tipo_detalle', 120)->nullable()->after('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos_examen', function (Blueprint $table) {
            if (Schema::hasColumn('grupos_examen', 'tipo_detalle')) {
                $table->dropColumn('tipo_detalle');
            }
        });
    }
};

