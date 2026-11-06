<?php

namespace App\Http\Requests;

use App\Models\AreaFormacion;
use App\Models\GradoAreaFormacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MallaCurricularRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $malla = $this->route('malla_curricular');
        $mallaId = $malla instanceof GradoAreaFormacion ? $malla->id : $malla;

        $pensumId = $this->input('pensum_id');
        $gradoId = $this->input('grado_id');

        return [
            'pensum_id' => [
                'required',
                'exists:pensums,id',
            ],
            'grado_id' => [
                'required',
                'exists:grados,id',
            ],
            'area_formacion_id' => [
                'required',
                'exists:area_formacions,id',
                Rule::unique('grado_area_formacions', 'area_formacion_id')
                    ->where(function ($query) use ($pensumId, $gradoId) {
                        return $query->where('pensum_id', $pensumId)
                                     ->where('grado_id', $gradoId);
                    })
                    ->ignore($mallaId),
            ],
            'horas' => [
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],
            'nota_aprobatoria' => [
                'required',
                'numeric',
                'min:1',
                'max:20',
            ],
            'escala' => [
                'required',
                Rule::in(['numerica', 'literal']),
            ],
            'componente' => [
                'nullable',
                'string',
                'max:100',
            ],
            'orden' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'pensum_id.required' => 'Debe seleccionar un plan de estudio (pensum).',
            'pensum_id.exists'   => 'El plan de estudio seleccionado no es válido.',
            'grado_id.required'  => 'Debe seleccionar el grado o año escolar.',
            'grado_id.exists'    => 'El grado seleccionado no es válido.',
            'area_formacion_id.required' => 'Debe seleccionar el área de formación.',
            'area_formacion_id.exists'   => 'El área de formación seleccionada no es válida.',
            'area_formacion_id.unique'   => 'Esta área de formación ya se encuentra asignada a este grado en el pensum seleccionado.',
            'horas.integer' => 'La carga horaria debe ser un número entero.',
            'horas.min'     => 'Las horas semanales deben ser al menos 1.',
            'horas.max'     => 'Las horas semanales no pueden superar 20.',
            'nota_aprobatoria.required' => 'La nota aprobatoria es obligatoria.',
            'nota_aprobatoria.numeric'  => 'La nota aprobatoria debe ser un número válido.',
            'nota_aprobatoria.min'      => 'La nota mínima es 1.',
            'nota_aprobatoria.max'      => 'La nota máxima es 20.',
            'escala.required' => 'El tipo de escala evaluativa es obligatorio.',
            'escala.in'       => 'La escala solo puede ser "numerica" o "literal".',
            'componente.max'  => 'El componente no puede superar los 100 caracteres.',
            'orden.required'  => 'El número de orden en el boletín es obligatorio.',
            'orden.integer'   => 'El orden debe ser un número entero.',
            'orden.min'       => 'El orden no puede ser menor a 1.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('area_formacion_id') || $validator->errors()->has('escala')) {
                    return;
                }

                $escala = $this->input('escala');
                $areaId = $this->input('area_formacion_id');

                $area = AreaFormacion::find($areaId);
                if (!$area) {
                    return;
                }

                $sigla = strtoupper(trim((string) $area->siglas));
                $nombre = mb_strtoupper(trim((string) $area->nombre_area_formacion), 'UTF-8');

                // Regla de Negocio del MPPE:
                // Escala "literal" únicamente permitida en:
                // 1. Orientación y Convivencia (OC)
                // 2. Grupos de Creación, Recreación y Producción (PGCRP)
                // 3. Orientación Vocacional (OV)
                $esMateriaPermitidaLiteral = in_array($sigla, ['OC', 'PGCRP', 'GCRP', 'OV'])
                    || str_contains($nombre, 'CONVIVENCIA')
                    || str_contains($nombre, 'CREACIÓN, RECREACIÓN')
                    || str_contains($nombre, 'CREACION, RECREACION')
                    || str_contains($nombre, 'ORIENTACIÓN VOCACIONAL')
                    || str_contains($nombre, 'ORIENTACION VOCACIONAL');

                if ($escala === 'literal' && !$esMateriaPermitidaLiteral) {
                    $validator->errors()->add(
                        'escala',
                        "Por normativa del MPPE, la escala literal solo está permitida para Orientación y Convivencia (OC), Grupos de Creación (PGCRP) y Orientación Vocacional. Para {$area->nombre_area_formacion} debe ser numérica."
                    );
                }
            },
        ];
    }
}

