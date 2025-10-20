<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $table) {
            if (!Schema::hasColumn('perfil_postulantes','fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('correo_contacto');
            }
            if (!Schema::hasColumn('perfil_postulantes','edad')) {
                $table->unsignedTinyInteger('edad')->nullable()->after('fecha_nacimiento');
            }

            // Extras (crea sólo los que no tengas aún)
            foreach ([
                ['curp','string',20],
                ['correo_alternativo','string',255],
                ['lugar_nacimiento','string',120],
                ['preparatoria','string',160],
                ['promedio_general','decimal',[4,2]],
                ['sexo','string',20],
                ['egreso_prepa_anio','integer',null],
                ['documento_terminacion','string',50],
                ['tipo_sangre','string',10],
                ['estado_salud','string',100],
                ['alergias','text',null],
                ['medicamentos','text',null],
                ['contacto_emergencia_nombre','string',160],
                ['contacto_emergencia_tel','string',30],
                ['info_adicional','text',null],
            ] as $col) {
                [$name,$type,$len] = $col;
                if (!Schema::hasColumn('perfil_postulantes',$name)) {
                    match($type) {
                        'string'  => $table->string($name, $len)->nullable(),
                        'decimal' => $table->decimal($name, $len[0], $len[1])->nullable(),
                        'integer' => $table->integer($name)->nullable(),
                        'text'    => $table->text($name)->nullable(),
                        default   => null
                    };
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('perfil_postulantes', function (Blueprint $table) {
            // Opcional: drop de columnas si lo necesitas
        });
    }
};
