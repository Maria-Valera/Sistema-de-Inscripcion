<?php

namespace App\Http\Requests;

use App\Models\AutoridadFirmante;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AutoridadFirmanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'encargado' => $this->boolean('encargado'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cargo' => [
                'required',
                Rule::in([
                    'director',
                    'subdirector_academico',
                    'coordinador_control_estudios',
                    'coordinador_evaluacion',
                    'docente_firmante_mppe',
                ]),
            ],
            'persona_id' => ['required', 'exists:personas,id'],
            'encargado' => ['nullable', 'boolean'],
            'gaceta' => ['nullable', 'string', 'max:100'],
            'desde' => ['required', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('desde') || $validator->errors()->has('hasta') || $validator->errors()->has('cargo') || $validator->errors()->has('persona_id')) {
                return;
            }

            $cargo = $this->input('cargo');
            $personaId = $this->input('persona_id');
            $desde = $this->input('desde');
            $hasta = $this->input('hasta');
            
            $paramAutoridad = $this->route('autoridad_firmante');
            $ignorarId = $paramAutoridad instanceof AutoridadFirmante ? $paramAutoridad->id : $paramAutoridad;

            $anioId = AnioEscolarSeleccionado::id();
            if (!$anioId && $paramAutoridad instanceof AutoridadFirmante) {
                $anioId = $paramAutoridad->anio_escolar_id;
            }

            if (!$anioId || !$cargo || !$desde) {
                return;
            }

            // 1. Regla para cargos singulares (director, subdirector_academico, coordinador_control_estudios, coordinador_evaluacion):
            // Ningún cargo de este tipo puede tener dos personas con fechas solapadas en el mismo año escolar.
            if ($cargo !== 'docente_firmante_mppe') {
                $solapado = AutoridadFirmante::where('anio_escolar_id', $anioId)
                    ->where('cargo', $cargo)
                    ->when($ignorarId, fn($q) => $q->where('id', '!=', $ignorarId))
                    ->where(function ($query) use ($desde, $hasta) {
                        if ($hasta) {
                            $query->where('desde', '<=', $hasta)
                                  ->where(function ($sub) use ($desde) {
                                      $sub->whereNull('hasta')
                                          ->orWhere('hasta', '>=', $desde);
                                  });
                        } else {
                            $query->whereNull('hasta')
                                  ->orWhere('hasta', '>=', $desde);
                        }
                    })
                    ->first();

                if ($solapado) {
                    $cargoNombre = AutoridadFirmante::CARGOS[$cargo] ?? $cargo;
                    $personaExistente = $solapado->persona?->nombre_completo ?? 'otra persona';
                    $rangoExistente = $solapado->desde?->format('d/m/Y') . ' al ' . ($solapado->hasta ? $solapado->hasta->format('d/m/Y') : 'Indefinido');
                    $validator->errors()->add(
                        'cargo',
                        "Ya existe una autoridad asignada como '{$cargoNombre}' ({$personaExistente}) en este año escolar con vigencia solapada ({$rangoExistente})."
                    );
                }
            } else {
                // 2. Regla para docente_firmante_mppe:
                // No permitir duplicar la misma persona en el mismo cargo con fechas solapadas.
                $solapadoDocente = AutoridadFirmante::where('anio_escolar_id', $anioId)
                    ->where('cargo', $cargo)
                    ->where('persona_id', $personaId)
                    ->when($ignorarId, fn($q) => $q->where('id', '!=', $ignorarId))
                    ->where(function ($query) use ($desde, $hasta) {
                        if ($hasta) {
                            $query->where('desde', '<=', $hasta)
                                  ->where(function ($sub) use ($desde) {
                                      $sub->whereNull('hasta')
                                          ->orWhere('hasta', '>=', $desde);
                                  });
                        } else {
                            $query->whereNull('hasta')
                                  ->orWhere('hasta', '>=', $desde);
                        }
                    })
                    ->first();

                if ($solapadoDocente) {
                    $validator->errors()->add(
                        'persona_id',
                        "Esta persona ya está registrada como Docente Firmante MPPE con un período de vigencia que se solapa con las fechas indicadas."
                    );
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cargo' => 'Cargo',
            'persona_id' => 'Persona',
            'encargado' => 'Condición de Encargado',
            'gaceta' => 'Gaceta Oficial / Resolución',
            'desde' => 'Vigencia Desde',
            'hasta' => 'Vigencia Hasta',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cargo.required' => 'El campo Cargo es obligatorio.',
            'cargo.in' => 'El cargo seleccionado no es válido.',
            'persona_id.required' => 'El campo Persona es obligatorio.',
            'persona_id.exists' => 'La persona seleccionada no está registrada en el sistema.',
            'desde.required' => 'El campo Vigencia Desde es obligatorio.',
            'desde.date' => 'El campo Vigencia Desde debe ser una fecha válida.',
            'hasta.date' => 'El campo Vigencia Hasta debe ser una fecha válida.',
            'hasta.after_or_equal' => 'El campo Vigencia Hasta debe ser una fecha posterior o igual a Vigencia Desde.',
        ];
    }
}

