<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Convocatoria;

class ConvocatoriaSeeder extends Seeder
{
    public function run(): void
    {
        Convocatoria::updateOrCreate(
            ['titulo' => 'Convocatoria Licenciatura en Enfermería 2025'],
            [
                'descripcion' => "Formación profesional de 8 semestres más 1 año de servicio social. Con enfoque clínico y comunitario.",
                'estatus' => 'activa',
                'fecha_inicio' => '2025-05-14',
                'fecha_fin' => '2025-06-27',

                // Costos
                'precio_ficha' => 600,
                'precio_inscripcion' => 1500,
                'precio_mensualidad' => 3740,

                // Cupo
                'cupo_total' => 120,

                // Portada demo
                'portada_path' => 'https://images.unsplash.com/photo-1584982751632-5f1df0a1cfdf',
                'ubicacion' => 'Calle 11, Plutarco Elías Calles #8930, Zona Centro, Tijuana B.C.',

                // Contacto
                'telefono_1' => '(664) 684 9826',
                'telefono_2' => '(664) 687 4520',
                'correo_1' => 'admision@cruzroja-tijuana.edu.mx',
                'correo_2' => 'informes@cruzroja-tijuana.edu.mx',
                'horario_atencion' => 'Lunes a viernes 8:00–17:00',

                // Programa
                'duracion' => '8 semestres + 1 año de servicio social',
                'certificaciones_adicionales' => 'BLS (3er semestre), ACLS (7º semestre)',
                'horario_matutino' => '07:00–14:00',
                'horario_vespertino' => '14:00–21:00',

                // Requisitos
                'requisitos_generales' => "- Certificado de preparatoria\n- Acta de nacimiento\n- CURP\n- Identificación oficial",
                'requisitos_examen_entrevista' => "- Identificación con foto\n- Ficha de pago\n- Puntualidad",
                'documentos_requeridos' => "- Acta de nacimiento\n- CURP\n- Certificado de bachillerato\n- 2 fotos tamaño infantil\n- Certificado médico\n- Comprobante de domicilio",

                // Fechas clave
                'fecha_publicacion_resultados' => '2025-07-25',
                'fecha_inicio_clases' => '2025-08-25',

                // Proceso de admisión
                'fecha_entrega_solicitudes_inicio' => '2025-05-14',
                'fecha_entrega_solicitudes_fin' => '2025-06-27',
                'fecha_psicometrico_inicio' => '2025-07-01',
                'fecha_psicometrico_fin' => '2025-07-04',
                'fecha_examen_conocimientos' => '2025-07-10',
                'fecha_curso_propedeutico_inicio' => '2025-08-25',
                'fecha_curso_propedeutico_fin' => '2025-08-29',

                // Galería demo
                'galeria_urls' => [
                    'https://images.unsplash.com/photo-1550792436-0a90b97da8a0',
                    'https://images.unsplash.com/photo-1526256262350-7da7584cf5eb'
                ],

                'notas' => "Resultados publicados en el portal. Becas y descuentos por pronto pago disponibles.",
            ]
        );
    }
}
