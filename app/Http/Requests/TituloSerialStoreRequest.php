<?php

namespace App\Http\Requests;

use App\Support\AnioEscolarSeleccionado;
use Illuminate\Foundation\Http\FormRequest;

// Validaciones de M6 al registrar seriales: se pega la lista que entrega la Zona Educativa, uno por línea.
class TituloSerialStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    /** Convierte el texto pegado en una lista: sin espacios, en mayúsculas y sin líneas vacías. */
    protected function prepareForValidation(): void
    {
        $lista = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('seriales')))
            ->map(fn ($linea) => strtoupper(trim($linea)))
            ->filter()
            ->values()
            ->all();

        $this->merge(['lista' => $lista, 'anio_escolar_id' => AnioEscolarSeleccionado::id()]);
    }

    public function rules(): array
    {
        return [
            'anio_escolar_id' => ['required', 'exists:anio_escolars,id'],
            'seriales' => ['required', 'string'],
            'lista' => ['required', 'array', 'min:1', 'max:500'],
            'lista.*' => ['regex:/^[A-Z0-9-]{3,30}$/', 'distinct', 'unique:titulo_seriales,serial'],
            'fecha_recepcion' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'anio_escolar_id.required' => 'No hay un año escolar seleccionado.',
            'seriales.required' => 'Pega al menos un serial (uno por línea).',
            'lista.required' => 'Pega al menos un serial (uno por línea).',
            'lista.max' => 'No se pueden registrar más de 500 seriales de una vez.',
            'lista.*.regex' => 'El serial ":input" no es válido: usa solo letras, números y guiones (de 3 a 30 caracteres).',
            'lista.*.distinct' => 'El serial ":input" está repetido en la lista.',
            'lista.*.unique' => 'El serial ":input" ya está registrado.',
            'fecha_recepcion.date' => 'La fecha de recepción no es válida.',
            'fecha_recepcion.before_or_equal' => 'La fecha de recepción no puede ser futura.',
        ];
    }
}
