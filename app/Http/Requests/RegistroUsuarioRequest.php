<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistroUsuarioRequest extends FormRequest
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
            'tipo_documento_id' => ['required', 'exists:tipo_documentos,id'],
            'numero_documento'  => ['required', 'string', 'max:20'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'          => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo_documento_id.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento_id.exists'   => 'El tipo de documento seleccionado no es válido.',
            'numero_documento.required'  => 'El número de documento es obligatorio.',
            'numero_documento.string'    => 'El número de documento debe ser una cadena de texto.',
            'numero_documento.max'       => 'El número de documento no puede tener más de 20 caracteres.',
            'email.required'             => 'El correo electrónico es obligatorio.',
            'email.string'               => 'El correo electrónico debe ser una cadena de texto.',
            'email.email'                => 'El formato del correo electrónico no es válido.',
            'email.max'                  => 'El correo electrónico no puede tener más de 255 caracteres.',
            'email.unique'               => 'Este correo electrónico ya está registrado.',
            'password.required'          => 'La contraseña es obligatoria.',
            'password.string'            => 'La contraseña debe ser una cadena de texto.',
            'password.confirmed'         => 'La confirmación de la contraseña no coincide.',
            'password.min'               => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters'           => 'La contraseña debe contener al menos una letra.',
            'password.numbers'           => 'La contraseña debe contener al menos un número.',
        ];
    }
}
