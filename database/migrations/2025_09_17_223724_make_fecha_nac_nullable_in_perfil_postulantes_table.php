<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('perfil_postulantes','fecha_nac')) {
            Schema::table('perfil_postulantes', function (Blueprint $t) {
                $t->date('fecha_nac')->nullable()->change();
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('perfil_postulantes','fecha_nac')) {
            Schema::table('perfil_postulantes', function (Blueprint $t) {
                $t->date('fecha_nac')->nullable(false)->change();
            });
        }
    }
};

