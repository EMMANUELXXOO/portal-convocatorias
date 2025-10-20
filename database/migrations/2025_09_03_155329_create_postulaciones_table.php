<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('postulaciones')) {
            Schema::create('postulaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
                $table->string('estatus')->default('pendiente')->index();
                $table->timestamps();
            });
        } else {
            // Opcional: alinear si la tabla existe pero está incompleta
            Schema::table('postulaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('postulaciones', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('postulaciones', 'convocatoria_id')) {
                    $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('postulaciones', 'estatus')) {
                    $table->string('estatus')->default('pendiente')->index();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};

