<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrupoEstableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('codigo') && $this->codigo !== null) {
            $this->merge([
                'codigo' => strtoupper(trim((string) $this->codigo)) ?: null,
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->input('id');

        return [
            'codigo' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('grupo_estables', 'codigo')
                    ->where('status', true)
                    ->ignore($id),
            ],
            'nombre_grupo_estable' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupo_estables', 'nombre_grupo_estable')
                    ->where('status', true)
                    ->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.max' => 'El código no debe superar los 20 caracteres.',
            'codigo.unique' => 'Ya existe un grupo estable activo con este código.',
            'nombre_grupo_estable.required' => 'El nombre del grupo estable es obligatorio.',
            'nombre_grupo_estable.max' => 'El nombre no debe superar los 255 caracteres.',
            'nombre_grupo_estable.unique' => 'Ya existe un grupo estable activo con este nombre.',
        ];
    }
}

