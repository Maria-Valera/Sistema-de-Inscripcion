<?php

namespace App\Http\Requests;

use App\Models\Dia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ConfiguracionHorarioRequest extends FormRequest
{
    /**
     * Determina si el usuario tiene permiso para ejecutar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la configuración global de horario.
     */
    public function rules(): array
    {
        return [
            'hora_inicio' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'numero_bloques' => ['required', 'integer', 'min:1', 'max:12'],
            'duracion_bloque_min' => ['required', 'integer', 'min:1', 'max:300'],
            'minutos_entre_bloques' => ['nullable', 'integer', 'min:0', 'max:120'],
            'recesos' => ['nullable', 'array'],
            'recesos.*.despues_de_bloque' => ['nullable', 'integer', 'min:1', 'distinct'],
            'recesos.*.duracion_min' => ['nullable', 'integer', 'min:1', 'max:120'],
            'reservados' => ['nullable', 'array'],
            'reservados.*.dia' => ['nullable', 'integer', 'min:1', 'max:5'],
            'reservados.*.desde' => ['nullable', 'integer', 'min:1'],
            'reservados.*.hasta' => ['nullable', 'integer', 'min:1'],
            'reservados.*.bloque' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Mensajes descriptivos de error.
     */
    public function messages(): array
    {
        return [
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'numero_bloques.required' => 'El número de bloques por día es obligatorio.',
            'numero_bloques.min' => 'Debe haber al menos 1 bloque por día.',
            'duracion_bloque_min.required' => 'La duración del bloque es obligatoria.',
            'duracion_bloque_min.min' => 'La duración debe ser de al menos 1 minuto.',
            'recesos.*.despues_de_bloque.distinct' => 'Ya existe un receso asignado a este bloque.',
        ];
    }

    /**
     * Validaciones personalizadas posteriores (detección de solapamientos).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $reservados = $this->input('reservados', []);
            if (!is_array($reservados)) {
                return;
            }

            $ocupados = []; // "dia_bloque" => true
            foreach ($reservados as $index => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $dia = isset($item['dia']) ? (int) $item['dia'] : null;
                $desde = isset($item['desde']) ? (int) $item['desde'] : (isset($item['bloque']) ? (int) $item['bloque'] : null);
                $hasta = isset($item['hasta']) ? (int) $item['hasta'] : $desde;

                if ($dia === null || $desde === null || $hasta === null) {
                    continue;
                }

                if ($desde > $hasta) {
                    $validator->errors()->add("reservados.{$index}.desde", 'El bloque inicial no puede ser mayor al bloque final.');
                    $validator->errors()->add('reservados', 'El bloque inicial no puede ser mayor al bloque final.');
                    continue;
                }

                for ($b = $desde; $b <= $hasta; $b++) {
                    $key = "{$dia}_{$b}";
                    if (isset($ocupados[$key])) {
                        $validator->errors()->add("reservados.{$index}.desde", 'El bloque reservado se cruza con uno existente.');
                        $validator->errors()->add('reservados', 'El bloque reservado se cruza con uno existente.');
                        break 2;
                    }
                    $ocupados[$key] = true;
                }
            }
        });
    }
}
