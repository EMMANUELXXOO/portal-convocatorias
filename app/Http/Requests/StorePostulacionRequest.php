<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostulacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // requiere login
    }

    public function rules(): array
    {
        // No pedimos nada porque todo lo llenamos en el controlador
        return [];
    }
}

