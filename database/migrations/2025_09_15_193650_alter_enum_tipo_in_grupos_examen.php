<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ajusta la lista a las que realmente usas en la app
        DB::statement("
            ALTER TABLE grupos_examen
            MODIFY COLUMN tipo ENUM(
                'psicometrico',
                'conocimiento',
                'entrevista',
                'diplomado',
                'primeros_auxilios',
                'aha',
                'capacitacion',
                'otro'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        
        // Si no, puedes dejarlo igual o restaurar una lista mínima.
        DB::statement("
            ALTER TABLE grupos_examen
            MODIFY COLUMN tipo ENUM(
                'psicometrico',
                'conocimiento',
                'diplomado',
                'primeros_auxilios',
                'aha',
                'capacitacion',
                'otro'
            ) NOT NULL
        ");
    }
};
