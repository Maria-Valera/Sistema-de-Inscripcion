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
            'cargo' => ['required', Rule::in(array_keys(AutoridadFirmante::CARGOS))],
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
            $cargo = $this->input('cargo');
            $desde = $this->input('desde');
            $hasta = $this->input('hasta');
            
            $paramAutoridad = $this->route('autoridad_firmante');
            $ignorarId = $paramAutoridad instanceof AutoridadFirmante ? $paramAutoridad->id : $paramAutoridad;

            $anioId = AnioEscolarSeleccionado::id();
            if (!$anioId && $paramAutoridad instanceof AutoridadFirmante) {
                $anioId = $paramAutoridad->anio_escolar_id;
            }

            // Regla de negocio: si el cargo NO es 'docente_firmante_mppe', debe ser único por periodo (sin solapamiento)
            if ($cargo && $cargo !== 'docente_firmante_mppe' && $desde && $anioId) {
                $solapado = AutoridadFirmante::where('anio_escolar_id', $anioId)
                    ->where('cargo', $cargo)
                    ->when($ignorarId, fn($q) => $q->where('id', '!=', $ignorarId))
                    ->where(function ($q) use ($desde, $hasta) {
                        if ($hasta) {
                            $q->where('desde', '<=', $hasta);
                        }
                        $q->where(function ($sub) use ($desde) {
                            $sub->whereNull('hasta')
                                ->orWhere('hasta', '>=', $desde);
                        });
                    })
                    ->exists();

                if ($solapado) {
                    $cargoNombre = AutoridadFirmante::CARGOS[$cargo] ?? $cargo;
                    $validator->errors()->add(
                        'cargo',
                        "Ya existe una autoridad asignada como '{$cargoNombre}' en este año escolar cuyo período de vigencia se solapa con las fechas indicadas."
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
            'cargo' => 'cargo',
            'persona_id' => 'persona',
            'encargado' => 'condición de encargado',
            'gaceta' => 'número de gaceta o resolución',
            'desde' => 'fecha de inicio',
            'hasta' => 'fecha de culminación',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cargo.required' => 'Debe seleccionar un cargo.',
            'cargo.in' => 'El cargo seleccionado no es válido.',
            'persona_id.required' => 'Debe seleccionar a una persona.',
            'persona_id.exists' => 'La persona seleccionada no está registrada en el sistema.',
            'desde.required' => 'La fecha de inicio de vigencia es obligatoria.',
            'desde.date' => 'La fecha de inicio debe ser una fecha válida.',
            'hasta.date' => 'La fecha de culminación debe ser una fecha válida.',
            'hasta.after_or_equal' => 'La fecha de culminación debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}

