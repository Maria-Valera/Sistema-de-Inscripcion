<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Paso 1 del asistente de año nuevo: las fechas del año escolar (queda activo al guardar todo).
class AsistenteAnioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    public function rules(): array
    {
        return [
            'inicio_anio_escolar' => ['required', 'date'],
            'cierre_anio_escolar' => ['required', 'date', 'after:inicio_anio_escolar'],
            'extencion_anio_escolar' => ['nullable', 'date', 'after:cierre_anio_escolar'],
        ];
    }

    public function messages(): array
    {
        return [
            'inicio_anio_escolar.required' => 'La fecha de inicio del año escolar es obligatoria.',
            'inicio_anio_escolar.date' => 'La fecha de inicio debe ser una fecha válida.',
            'cierre_anio_escolar.required' => 'La fecha de cierre del año escolar es obligatoria.',
            'cierre_anio_escolar.date' => 'La fecha de cierre debe ser una fecha válida.',
            'cierre_anio_escolar.after' => 'La fecha de cierre debe ser posterior a la fecha de inicio.',
            'extencion_anio_escolar.date' => 'La fecha de extensión debe ser una fecha válida.',
            'extencion_anio_escolar.after' => 'La fecha de extensión debe ser posterior a la fecha de cierre.',
        ];
    }
}
