<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_tipo_detalle_to_grupo_examens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('grupos_examen', function (Blueprint $table) {
            $table->string('tipo_detalle', 120)->nullable()->after('tipo');
        });
    }
    public function down(): void {
        Schema::table('grupos_examen', function (Blueprint $table) {
            $table->dropColumn('tipo_detalle');
        });
    }
};

