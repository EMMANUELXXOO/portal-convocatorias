<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('perfiles_postulantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nombre_completo');
            $table->date('fecha_nac');
            $table->unsignedTinyInteger('edad'); // se llenará en save()
            $table->string('telefono', 20);
            $table->string('correo_contacto');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('perfiles_postulantes');
    }
};