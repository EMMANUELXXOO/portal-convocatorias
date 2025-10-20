<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grupo_postulacion', function (Blueprint $table) {
            // agrega created_at y updated_at (nullable por si ya hay datos)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('grupo_postulacion', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
