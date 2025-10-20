<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::create('grupo_postulacion', function (Blueprint $table) {
        $table->id();

        $table->foreignId('grupo_examen_id')
              ->constrained('grupos_examen')
              ->cascadeOnDelete();

        $table->foreignId('postulacion_id')
              ->constrained('postulaciones')
              ->cascadeOnDelete();

        $table->timestamp('asignado_en')->nullable();

        // Evita duplicados
        $table->unique(['grupo_examen_id','postulacion_id']);

        // Si quieres timestamps de Laravel en el pivote
        // $table->timestamps();
    });

}
public function down(): void
{
    Schema::dropIfExists('grupo_postulacion');
}


};
