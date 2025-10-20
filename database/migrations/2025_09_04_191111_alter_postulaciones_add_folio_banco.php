<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('postulaciones', 'folio_banco')) {
                $table->string('folio_banco', 64)->nullable()->after('referencia_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            if (Schema::hasColumn('postulaciones', 'folio_banco')) {
                $table->dropColumn('folio_banco');
            }
        });
    }
};
