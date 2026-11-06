<?php

namespace App\Http\Requests;

use App\Models\Pensum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PensumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('codigo')) {
            $this->merge([
                'codigo' => trim((string) $this->codigo),
            ]);
        }

        if ($this->has('nombre')) {
            $this->merge([
                'nombre' => trim((string) $this->nombre),
            ]);
        }

        if ($this->has('descripcion')) {
            $this->merge([
                'descripcion' => trim((string) $this->descripcion),
            ]);
        }

        // Si anio_fin viene vacío o con solo espacios, convertir a null (vigente)
        if ($this->has('anio_fin') && trim((string) $this->anio_fin) === '') {
            $this->merge([
                'anio_fin' => null,
            ]);
        }
    }

    public function rules(): array
    {
        $pensum = $this->route('pensum');
        $pensumId = $pensum instanceof Pensum ? $pensum->id : $pensum;

        return [
            'codigo' => [
                'required',
                'digits:5',
                Rule::unique('pensums', 'codigo')->ignore($pensumId),
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:500',
            ],
            'anio_inicio' => [
                'required',
                'integer',
                'min:1970',
                'max:2100',
            ],
            'anio_fin' => [
                'nullable',
                'integer',
                'min:1970',
                'max:2100',
                'gte:anio_inicio',
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
            'codigo.required' => 'El código del plan de estudio es obligatorio.',
            'codigo.digits'   => 'El código del plan debe contener exactamente 5 dígitos numéricos (ej. 31059).',
            'codigo.unique'   => 'Ya existe un plan de estudio registrado con este código.',
            'nombre.required' => 'El nombre del plan de estudio es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'anio_inicio.required' => 'El año de inicio de vigencia es obligatorio.',
            'anio_inicio.integer'  => 'El año de inicio debe ser un número entero.',
            'anio_inicio.min'      => 'El año de inicio no puede ser anterior a 1970.',
            'anio_inicio.max'      => 'El año de inicio no puede ser posterior a 2100.',
            'anio_fin.integer'     => 'El año de fin debe ser un número entero.',
            'anio_fin.min'         => 'El año de fin no puede ser anterior a 1970.',
            'anio_fin.max'         => 'El año de fin no puede ser posterior a 2100.',
            'anio_fin.gte'         => 'El año de fin de vigencia debe ser mayor o igual al año de inicio.',
        ];
    }
}

