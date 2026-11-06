<?php

namespace App\Http\Requests;

use App\Enums\ReglaRedondeo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validaciones de M3. Las usan store() y update() de ConfiguracionEvaluacionController.
class ConfiguracionEvaluacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    public function rules(): array
    {
        // En update() la ruta trae la configuración; en store() es null.
        $actual = $this->route('configuracionEvaluacion');

        return [
            'anio_escolar_id' => [
                'required', 'exists:anio_escolars,id',
                Rule::unique('configuracion_evaluacions', 'anio_escolar_id')->ignore($actual?->id),
            ],
            'nota_minima' => ['required', 'integer', 'min:0', 'max:99'],
            'nota_maxima' => ['required', 'integer', 'max:100', 'gt:nota_minima'],
            'nota_aprobatoria' => ['required', 'integer', 'gt:nota_minima', 'lt:nota_maxima'],
            'regla_redondeo' => ['required', Rule::enum(ReglaRedondeo::class)],
            'num_lapsos' => ['required', 'integer', 'min:2', 'max:4'],
            'num_cortes' => ['required', 'integer', 'min:1', 'max:10'],
            'puntos_corte' => ['required', 'integer', 'min:1', 'max:100'],
            'momentos_quedada' => ['required', 'integer', 'min:1', 'max:6'],
            'max_areas_pendientes' => ['required', 'integer', 'min:0', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'anio_escolar_id.required' => 'Debes elegir el año escolar.',
            'anio_escolar_id.exists' => 'El año escolar elegido no existe.',
            'anio_escolar_id.unique' => 'Ese año escolar ya tiene su configuración de evaluación.',
            'nota_maxima.gt' => 'La nota máxima debe ser mayor que la nota mínima.',
            'nota_aprobatoria.gt' => 'La nota aprobatoria debe ser mayor que la nota mínima.',
            'nota_aprobatoria.lt' => 'La nota aprobatoria debe ser menor que la nota máxima.',
            'regla_redondeo.enum' => 'La regla de redondeo elegida no es válida.',
            'num_lapsos.min' => 'Debe haber al menos 2 lapsos.',
            'num_lapsos.max' => 'No puede haber más de 4 lapsos.',
            'max_areas_pendientes.max' => 'No pueden ser más de 5 áreas pendientes.',
            '*.required' => 'Este campo es obligatorio.',
            '*.integer' => 'Debe ser un número entero.',
            '*.min' => 'El valor es demasiado bajo.',
            '*.max' => 'El valor es demasiado alto.',
        ];
    }
}
