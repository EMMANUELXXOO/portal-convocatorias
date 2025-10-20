<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla ya existe, no hagas nada
        if (Schema::hasTable('perfil_postulantes')) {
            return;
        }

        Schema::create('perfil_postulantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nombre_completo');
            $table->date('fecha_nac');
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('telefono', 20);
            $table->string('correo_contacto');
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_postulantes');
    }
};
