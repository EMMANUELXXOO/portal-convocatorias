<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            if (!Schema::hasColumn('convocatorias', 'carousel_enabled')) {
                $table->boolean('carousel_enabled')->default(false)->after('galeria_urls');
            }
            if (!Schema::hasColumn('convocatorias', 'hero_fit')) {
                $table->string('hero_fit', 16)->default('cover')->after('carousel_enabled'); // 'cover' | 'contain'
            }
            // Si tu DB no tuviera galeria_urls como JSON/longText, puedes asegurarlo aquí:
            // if (!Schema::hasColumn('convocatorias','galeria_urls')) {
            //     $table->json('galeria_urls')->nullable()->after('portada_path');
            // }
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            if (Schema::hasColumn('convocatorias', 'hero_fit')) {
                $table->dropColumn('hero_fit');
            }
            if (Schema::hasColumn('convocatorias', 'carousel_enabled')) {
                $table->dropColumn('carousel_enabled');
            }
        });
    }
};
