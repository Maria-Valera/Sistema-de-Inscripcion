<?php

namespace App\Http\Requests;

use App\Models\Mencion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MencionRequest extends FormRequest
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

        if ($this->has('ciclo')) {
            $this->merge([
                'ciclo' => trim((string) $this->ciclo),
            ]);
        }
    }

    public function rules(): array
    {
        $mencion = $this->route('mencione'); // Laravel resource singular de menciones
        $mencionId = $mencion instanceof Mencion ? $mencion->id : $mencion;

        return [
            'codigo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('menciones', 'codigo')->ignore($mencionId),
            ],
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],
            'ciclo' => [
                'required',
                'string',
                'max:20',
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
            'codigo.required' => 'El código oficial de la mención es obligatorio (ej. 01).',
            'codigo.max'      => 'El código no puede superar los 10 caracteres.',
            'codigo.unique'   => 'Ya existe una mención registrada con este código.',
            'nombre.required' => 'El nombre de la mención es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'ciclo.required'  => 'El código de ciclo del MPPE es obligatorio (ej. 50).',
            'ciclo.max'       => 'El código de ciclo no puede superar los 20 caracteres.',
        ];
    }
}

