<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnioEscolarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inicio_anio_escolar' => ['required', 'date'],
            'cierre_anio_escolar' => ['required', 'date', 'after:inicio_anio_escolar'],
            'extencion_anio_escolar' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['Activo', 'Inactivo', 'Extendido'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'inicio_anio_escolar' => 'fecha de inicio',
            'cierre_anio_escolar' => 'fecha de cierre',
            'extencion_anio_escolar' => 'fecha de extensión',
            'status' => 'estado',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'inicio_anio_escolar.required' => 'La fecha de inicio del año escolar es obligatoria.',
            'inicio_anio_escolar.date' => 'La fecha de inicio debe ser una fecha válida.',
            'cierre_anio_escolar.required' => 'La fecha de cierre del año escolar es obligatoria.',
            'cierre_anio_escolar.date' => 'La fecha de cierre debe ser una fecha válida.',
            'cierre_anio_escolar.after' => 'La fecha de cierre debe ser posterior a la fecha de inicio.',
            'extencion_anio_escolar.date' => 'La fecha de extensión debe ser una fecha válida.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido (debe ser Activo, Inactivo o Extendido).',
        ];
    }
}

