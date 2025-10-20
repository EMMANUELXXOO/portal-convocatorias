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
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('action');            // e.g. convocatoria.update
        $table->string('entity_type')->nullable(); // App\Models\Convocatoria
        $table->unsignedBigInteger('entity_id')->nullable();
        $table->json('meta')->nullable();    // antes/después, IP, etc.
        $table->ipAddress('ip')->nullable();
        $table->string('agent')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
