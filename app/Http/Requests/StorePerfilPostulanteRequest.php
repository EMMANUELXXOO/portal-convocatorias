<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerfilPostulanteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a ejecutar esta request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación para la ficha del aspirante.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
  public function rules(): array
{
    return [
        'nombre_completo'         => ['required','string','max:255'],
        'curp'                    => ['nullable','string','max:18','regex:/^[A-Z0-9]{10,18}$/i'],
        'telefono'                => ['required','string','max:20'],
        'correo_contacto'         => ['required','email','max:255'],
        'correo_alternativo'      => ['nullable','email','max:255'],

        'lugar_nacimiento'        => ['nullable','string','max:255'],
        'preparatoria'            => ['nullable','string','max:255'],
        'promedio_general'        => ['nullable','numeric','min:0','max:100'],
        'sexo'                    => ['nullable', Rule::in(['masculino','femenino','otro'])],
        'fecha_nac'               => ['required','date','before:today'],
        'egreso_prepa_anio'       => ['nullable','integer','min:1950','max:'.(date('Y')+1)],
        'documento_terminacion'   => ['nullable', Rule::in(['constancia','certificado','kardex'])],

        'tipo_sangre'             => ['nullable','string','max:10'],
        'estado_salud'            => ['nullable','string','max:255'],
        'alergias'                => ['nullable','string','max:500'],
        'medicamentos'            => ['nullable','string','max:500'],

        'contacto_emergencia_nombre' => ['nullable','string','max:255'],
        'contacto_emergencia_tel'    => ['nullable','string','max:20'],

        'info_adicional'          => ['nullable','string','max:2000'],
    ];
}


    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'fecha_nac.required'       => 'La fecha de nacimiento es obligatoria.',
            'fecha_nac.before'         => 'La fecha de nacimiento debe ser anterior a hoy.',
            'telefono.required'        => 'El teléfono de contacto es obligatorio.',
            'telefono.regex'           => 'El teléfono contiene caracteres inválidos.',
            'correo_contacto.required' => 'El correo de contacto es obligatorio.',
            'correo_contacto.email'    => 'El correo de contacto no es válido.',
            'curp.regex'               => 'La CURP debe contener solo letras y números (10 a 18 caracteres).',
        ];
    }
}
