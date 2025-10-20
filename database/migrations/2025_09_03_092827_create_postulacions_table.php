<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('convocatoria_id')
                  ->constrained('convocatorias')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // Estado operativo
            $table->string('estatus', 32)->default('pendiente'); // pendiente|pago_pendiente|pagado|validada|rechazada

            // Operación
            $table->string('folio', 32)->unique();        // Identificador público para el aspirante
            $table->string('referencia_pago', 64)->nullable();
            $table->timestamp('fecha_pago')->nullable();

            // Auditoría
            $table->ipAddress('ip_registro')->nullable();
            $table->string('agente', 255)->nullable();

            $table->timestamps();

            // Reglas e índices
            $table->unique(['user_id', 'convocatoria_id']);      // 1 postulación por convocatoria
            $table->index(['convocatoria_id', 'estatus']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
