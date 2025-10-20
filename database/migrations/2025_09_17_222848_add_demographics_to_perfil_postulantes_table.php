<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $t) {
            if (!Schema::hasColumn('perfil_postulantes','genero')) {
                $t->string('genero', 20)->nullable()->after('user_id'); // ej: 'femenino','masculino','no_binario'
            }
            if (!Schema::hasColumn('perfil_postulantes','fecha_nacimiento')) {
                $t->date('fecha_nacimiento')->nullable()->after('genero');
            }
            if (!Schema::hasColumn('perfil_postulantes','preparatoria')) {
                $t->string('preparatoria', 191)->nullable()->after('fecha_nacimiento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $t) {
            if (Schema::hasColumn('perfil_postulantes','preparatoria')) {
                $t->dropColumn('preparatoria');
            }
            if (Schema::hasColumn('perfil_postulantes','fecha_nacimiento')) {
                $t->dropColumn('fecha_nacimiento');
            }
            if (Schema::hasColumn('perfil_postulantes','genero')) {
                $t->dropColumn('genero');
            }
        });
    }
};
