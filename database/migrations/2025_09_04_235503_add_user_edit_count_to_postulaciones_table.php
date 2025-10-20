<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('postulaciones', function (Blueprint $table) {
        $table->unsignedTinyInteger('user_edit_count')->default(0)->after('estatus');
        // Opcional: saber cuándo fue la primera edición del usuario
        $table->timestamp('user_first_edit_at')->nullable()->after('user_edit_count');
    });
}

public function down(): void
{
    Schema::table('postulaciones', function (Blueprint $table) {
        $table->dropColumn(['user_edit_count', 'user_first_edit_at']);
    });
}

};
