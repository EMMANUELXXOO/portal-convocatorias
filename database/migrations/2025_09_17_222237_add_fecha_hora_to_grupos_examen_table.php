<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos_examen', function (Blueprint $table) {
            // Campo principal para fecha/hora del examen o evento
            if (!Schema::hasColumn('grupos_examen', 'fecha_hora')) {
                $table->dateTime('fecha_hora')->nullable()->after('tipo');
            }

            // Campos opcionales de ubicación
            if (!Schema::hasColumn('grupos_examen', 'lugar')) {
                $table->string('lugar')->nullable()->after('fecha_hora');
            }
            if (!Schema::hasColumn('grupos_examen', 'aula')) {
                $table->string('aula')->nullable()->after('lugar');
            }
            if (!Schema::hasColumn('grupos_examen', 'sede')) {
                $table->string('sede')->nullable()->after('aula');
            }
            if (!Schema::hasColumn('grupos_examen', 'ubicacion')) {
                $table->string('ubicacion')->nullable()->after('sede');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos_examen', function (Blueprint $table) {
            $table->dropColumn(['fecha_hora','lugar','aula','sede','ubicacion']);
        });
    }
};
