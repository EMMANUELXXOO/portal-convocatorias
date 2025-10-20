<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   
  public function up(): void
{
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();          // ej: mail_from
        $table->json('payload')->nullable();      // guardamos objetos/pares
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('settings');
}

};
