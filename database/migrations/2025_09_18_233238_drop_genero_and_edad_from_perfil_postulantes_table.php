<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $table) {
            if (Schema::hasColumn('perfil_postulantes', 'genero')) {
                $table->dropColumn('genero');
            }
            if (Schema::hasColumn('perfil_postulantes', 'edad')) {
                $table->dropColumn('edad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $table) {
            $table->string('genero')->nullable();
            $table->integer('edad')->nullable();
        });
    }
};
