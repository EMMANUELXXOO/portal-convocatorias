<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->unsignedTinyInteger('recibo_reenvios')
                  ->default(0)
                  ->after('fecha_pago'); // 👈 justo después de fecha_pago
        });
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropColumn('recibo_reenvios');
        });
    }
};
