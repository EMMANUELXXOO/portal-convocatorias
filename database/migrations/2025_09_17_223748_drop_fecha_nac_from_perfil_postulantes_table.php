<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $t) {
            if (Schema::hasColumn('perfil_postulantes','fecha_nac')) {
                $t->dropColumn('fecha_nac');
            }
        });
    }
    public function down(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $t) {
            if (!Schema::hasColumn('perfil_postulantes','fecha_nac')) {
                $t->date('fecha_nac')->nullable(); // no podemos recrear constraints exactas sin conocerlas
            }
        });
    }
};
