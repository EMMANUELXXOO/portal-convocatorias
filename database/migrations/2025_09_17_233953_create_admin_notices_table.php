<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notices', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 160);
            $table->text('mensaje');
            $table->enum('nivel', ['info','success','warning','danger'])->default('info');
            $table->enum('audiencia', ['todos','aspirantes','admin'])->default('todos');
            $table->timestamp('visible_desde')->nullable();
            $table->timestamp('visible_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['activo','audiencia','visible_desde','visible_hasta'], 'notices_vigencia_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notices');
    }
};
