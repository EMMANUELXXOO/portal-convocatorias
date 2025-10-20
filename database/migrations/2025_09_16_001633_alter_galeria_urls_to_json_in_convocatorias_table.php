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
    // Requiere doctrine/dbal si vas a usar ->change()
    // composer require doctrine/dbal
    Schema::table('convocatorias', function (Blueprint $table) {
        $table->json('galeria_urls')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('convocatorias', function (Blueprint $table) {
        $table->text('galeria_urls')->nullable()->change();
    });
}

};
