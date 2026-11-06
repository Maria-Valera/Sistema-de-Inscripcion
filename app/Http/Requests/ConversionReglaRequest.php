<?php

namespace App\Http\Requests;

use App\Models\ConversionRegla;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ConversionReglaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $regla = $this->route('conversion_regla');
        $reglaId = $regla instanceof ConversionRegla ? $regla->id : $regla;

        $origenId = $this->input('pensum_origen_id');
        $destinoId = $this->input('pensum_destino_id');
        $gradoDestinoId = $this->input('grado_destino_id');

        return [
            'pensum_origen_id' => [
                'required',
                'exists:pensums,id',
                'different:pensum_destino_id',
            ],
            'pensum_destino_id' => [
                'required',
                'exists:pensums,id',
            ],
            'grado_destino_id' => [
                'required',
                'exists:grados,id',
            ],
            'area_destino_id' => [
                'required',
                'exists:area_formacions,id',
                Rule::unique('conversion_reglas', 'area_destino_id')
                    ->where(function ($query) use ($origenId, $destinoId, $gradoDestinoId) {
                        return $query->where('pensum_origen_id', $origenId)
                                     ->where('pensum_destino_id', $destinoId)
                                     ->where('grado_destino_id', $gradoDestinoId);
                    })
                    ->ignore($reglaId),
            ],
            'tipo_regla' => [
                'required',
                Rule::in(['directa', 'promedio', 'exonerada', 'no_cuenta']),
            ],
            'observacion' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
            'origenes' => [
                'nullable',
                'array',
            ],
            'origenes.*.grado_origen_id' => [
                'required_with:origenes',
                'exists:grados,id',
            ],
            'origenes.*.area_origen_id' => [
                'required_with:origenes',
                'exists:area_formacions,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'pensum_origen_id.required'  => 'El plan de origen es obligatorio.',
            'pensum_origen_id.exists'    => 'El plan de origen no es válido.',
            'pensum_origen_id.different' => 'El plan de origen y destino deben ser diferentes.',
            'pensum_destino_id.required' => 'El plan de destino es obligatorio.',
            'pensum_destino_id.exists'   => 'El plan de destino no es válido.',
            'grado_destino_id.required'  => 'El grado de destino es obligatorio.',
            'grado_destino_id.exists'    => 'El grado de destino no es válido.',
            'area_destino_id.required'   => 'El área de formación de destino es obligatoria.',
            'area_destino_id.exists'     => 'El área de formación de destino no es válida.',
            'area_destino_id.unique'     => 'Ya existe una regla de conversión para esta asignatura en la transición seleccionada.',
            'tipo_regla.required'        => 'Debe seleccionar el tipo de regla de conversión.',
            'tipo_regla.in'              => 'El tipo de regla seleccionado no es válido.',
            'observacion.max'            => 'La observación no puede superar los 255 caracteres.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $tipoRegla = $this->input('tipo_regla');
                $origenes = array_filter($this->input('origenes', []), function ($item) {
                    return !empty($item['area_origen_id']) && !empty($item['grado_origen_id']);
                });
                $cantOrigenes = count($origenes);

                // REGLAS CLAVE DEL MPPE (Circular 01 y DBML v2611):
                // 1. Directa = 1 área de origen
                if ($tipoRegla === 'directa' && $cantOrigenes !== 1) {
                    $validator->errors()->add(
                        'origenes',
                        'Una homologación directa debe asociar exactamente 1 área de origen.'
                    );
                }

                // 2. Promedio = 2 o más áreas de origen
                if ($tipoRegla === 'promedio' && $cantOrigenes < 2) {
                    $validator->errors()->add(
                        'origenes',
                        'Para calcular un promedio se requieren al menos 2 áreas de origen del plan anterior.'
                    );
                }

                // 3. Exonerada = ninguna área de origen (0)
                if ($tipoRegla === 'exonerada' && $cantOrigenes > 0) {
                    $validator->errors()->add(
                        'origenes',
                        'Una materia exonerada no debe tener materias de origen asociadas (se aprueba por equivalencia de plan sin nota).'
                    );
                }

                // 4. No se toma en cuenta = ninguna área de origen (0)
                if ($tipoRegla === 'no_cuenta' && $cantOrigenes > 0) {
                    $validator->errors()->add(
                        'origenes',
                        'Una materia no contemplada en el nuevo pensum no debe asociar materias de origen.'
                    );
                }
            },
        ];
    }
}

