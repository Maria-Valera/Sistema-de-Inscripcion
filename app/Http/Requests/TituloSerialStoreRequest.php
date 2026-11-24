<?php

namespace App\Http\Requests;

use App\Support\AnioEscolarSeleccionado;
use Illuminate\Foundation\Http\FormRequest;

// Validaciones de M6 al registrar seriales: a mano (la lista pegada, uno por línea) o por rango (prefijo + desde/hasta).
class TituloSerialStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    /** Un rango no puede tener más seriales que esto (lo mismo que la lista manual). */
    public const MAXIMO = 500;

    /**
     * Arma la lista de seriales, sea cual sea el modo:
     *  - manual: el texto pegado, uno por línea, sin espacios, en mayúsculas y sin líneas vacías;
     *  - rango: prefijo + cada número desde "desde" hasta "hasta", con los ceros a la izquierda de "desde".
     * Después las dos listas se validan igual.
     */
    protected function prepareForValidation(): void
    {
        $modo = $this->input('modo');
        $datos = ['anio_escolar_id' => AnioEscolarSeleccionado::id(), 'lista' => []];

        if ($modo === 'rango') {
            $prefijo = strtoupper(trim((string) $this->input('prefijo')));
            $desde = trim((string) $this->input('desde'));
            $hasta = trim((string) $this->input('hasta'));
            $datos += ['prefijo' => $prefijo, 'desde' => $desde, 'hasta' => $hasta];

            if ($this->rangoLegible($desde, $hasta)) {
                $total = (int) $hasta - (int) $desde + 1;
                $datos['rango_total'] = $total;

                // Solo se arma la lista si el rango es razonable (no se generan millones de seriales).
                if ($total >= 1 && $total <= self::MAXIMO) {
                    $datos['lista'] = collect(range((int) $desde, (int) $hasta))
                        ->map(fn ($n) => $prefijo . str_pad((string) $n, strlen($desde), '0', STR_PAD_LEFT))
                        ->all();
                }
            }
        } else {
            $datos['lista'] = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('seriales')))
                ->map(fn ($linea) => strtoupper(trim($linea)))
                ->filter()
                ->values()
                ->all();
        }

        $this->merge($datos);
    }

    /** Desde y hasta son números de 1 a 15 dígitos. */
    private function rangoLegible(string $desde, string $hasta): bool
    {
        return (bool) preg_match('/^\d{1,15}$/', $desde) && (bool) preg_match('/^\d{1,15}$/', $hasta);
    }

    public function rules(): array
    {
        $reglas = [
            'anio_escolar_id' => ['required', 'exists:anio_escolars,id'],
            'modo' => ['required', 'in:manual,rango'],
            'seriales' => ['exclude_unless:modo,manual', 'required', 'string'],
            'prefijo' => ['exclude_unless:modo,rango', 'nullable', 'regex:/^[A-Z0-9-]{0,20}$/'],
            'desde' => ['exclude_unless:modo,rango', 'required', 'regex:/^\d{1,15}$/'],
            'hasta' => ['exclude_unless:modo,rango', 'required', 'regex:/^\d{1,15}$/'],
            'lista' => ['array', 'max:' . self::MAXIMO],
            'lista.*' => ['regex:/^[A-Z0-9-]{3,30}$/', 'distinct', 'unique:titulo_seriales,serial'],
            'fecha_recepcion' => ['nullable', 'date', 'before_or_equal:today'],
        ];

        if ($this->input('modo') === 'manual') {
            $reglas['lista'][] = 'required';
            $reglas['lista'][] = 'min:1';
        }

        // El total del rango solo se revisa si desde y hasta son números.
        if ($this->input('modo') === 'rango' && $this->has('rango_total')) {
            $reglas['rango_total'] = ['integer', 'min:1', 'max:' . self::MAXIMO];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'anio_escolar_id.required' => 'No hay un año escolar seleccionado.',
            'modo.required' => 'Elige cómo registrar los seriales: manual o por rango.',
            'modo.in' => 'Elige cómo registrar los seriales: manual o por rango.',
            'prefijo.regex' => 'El prefijo solo puede tener letras, números y guiones (hasta 20).',
            'desde.required' => 'Escribe el número inicial del rango.',
            'desde.regex' => 'El número inicial debe tener solo dígitos (hasta 15).',
            'hasta.required' => 'Escribe el número final del rango.',
            'hasta.regex' => 'El número final debe tener solo dígitos (hasta 15).',
            'rango_total.min' => 'El número final no puede ser menor que el inicial.',
            'rango_total.max' => 'Un rango no puede tener más de 500 seriales.',
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
