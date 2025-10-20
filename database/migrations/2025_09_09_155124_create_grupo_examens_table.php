<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_examen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convocatoria_id')->constrained()->cascadeOnDelete();

            $table->enum('tipo', ['psicometrico','conocimiento']);
            $table->timestamp('fecha_hora');
            $table->string('lugar')->nullable();
            $table->unsignedInteger('capacidad');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_examen');
    }
};
