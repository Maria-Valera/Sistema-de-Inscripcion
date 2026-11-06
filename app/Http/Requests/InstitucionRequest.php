<?php

namespace App\Http\Requests;

use App\Models\Institucion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $institucion = $this->route('institucion');
        $institucionId = $institucion instanceof Institucion ? $institucion->id : $institucion;

        return [
            'codigo_plantel' => [
                'required',
                'string',
                'max:50',
                Rule::unique('instituciones', 'codigo_plantel')->ignore($institucionId),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'localidad_id' => ['nullable', 'integer', 'exists:localidads,id'],
            'zona_educativa' => ['nullable', 'string', 'max:100'],
            'director_persona_id' => ['nullable', 'integer', 'exists:personas,id'],
            'director_encargado' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'logo_ruta' => ['nullable', 'string', 'max:255'],
            'cdcee' => ['nullable', 'string', 'max:100'],
            'dependencia' => ['nullable', 'string', 'max:100'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_plantel.required' => 'El código del plantel es obligatorio.',
            'codigo_plantel.unique' => 'Este código de plantel ya se encuentra registrado.',
            'codigo_plantel.max' => 'El código no debe exceder los 50 caracteres.',
            'nombre.required' => 'El nombre de la institución es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'direccion.max' => 'La dirección no debe exceder los 500 caracteres.',
            'telefono.max' => 'El teléfono no debe exceder los 50 caracteres.',
            'localidad_id.exists' => 'La localidad seleccionada no es válida.',
            'director_persona_id.exists' => 'La persona seleccionada como director no es válida.',
            'logo.image' => 'El archivo del logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser un archivo de formato jpeg, png, jpg, webp o svg.',
            'logo.max' => 'El tamaño del logo no debe superar los 2MB.',
        ];
    }
}

