<?php

namespace App\Http\Requests;

use App\Enums\EstadoSerialTitulo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// Validaciones de M6 al editar un serial: su texto, su estado y, si se anula o devuelve, el motivo.
class TituloSerialUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['serial' => strtoupper(trim((string) $this->input('serial')))]);
    }

    public function rules(): array
    {
        $serial = $this->route('tituloSerial');
        $conMotivo = [EstadoSerialTitulo::Anulado->value, EstadoSerialTitulo::Devuelto->value];

        return [
            'serial' => [
                'required', 'regex:/^[A-Z0-9-]{3,30}$/',
                Rule::unique('titulo_seriales', 'serial')->ignore($serial->id),
            ],
            'fecha_recepcion' => ['nullable', 'date', 'before_or_equal:today'],
            'estado' => ['required', Rule::enum(EstadoSerialTitulo::class)],
            'motivo_anulacion' => ['nullable', 'string', 'max:255', Rule::requiredIf(in_array($this->input('estado'), $conMotivo, true))],
            'fecha_liquidacion' => [
                'nullable', 'date', 'before_or_equal:today',
                Rule::requiredIf($this->input('estado') === EstadoSerialTitulo::Devuelto->value),
            ],
        ];
    }

    /** Reglas que miran el registro actual: un serial asignado no cambia de número. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $serial = $this->route('tituloSerial');

            if ($serial->estado === EstadoSerialTitulo::Asignado && $this->input('serial') !== $serial->serial) {
                $validator->errors()->add('serial', 'Este serial ya está asignado a un título: su número no se puede cambiar.');
            }

            $recepcion = $this->input('fecha_recepcion');
            $liquidacion = $this->input('fecha_liquidacion');
            if ($recepcion && $liquidacion && $liquidacion < $recepcion) {
                $validator->errors()->add('fecha_liquidacion', 'La fecha de devolución no puede ser anterior a la de recepción.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'serial.required' => 'Debes indicar el serial.',
            'serial.regex' => 'El serial no es válido: usa solo letras, números y guiones (de 3 a 30 caracteres).',
            'serial.unique' => 'Ese serial ya está registrado.',
            'fecha_recepcion.date' => 'La fecha de recepción no es válida.',
            'fecha_recepcion.before_or_equal' => 'La fecha de recepción no puede ser futura.',
            'estado.enum' => 'El estado elegido no es válido.',
            'motivo_anulacion.required' => 'Para anular o devolver un serial debes indicar el motivo.',
            'motivo_anulacion.max' => 'El motivo no puede pasar de 255 caracteres.',
            'fecha_liquidacion.required' => 'Para marcar un serial como devuelto debes indicar la fecha de devolución.',
            'fecha_liquidacion.date' => 'La fecha de devolución no es válida.',
            'fecha_liquidacion.before_or_equal' => 'La fecha de devolución no puede ser futura.',
        ];
    }
}
