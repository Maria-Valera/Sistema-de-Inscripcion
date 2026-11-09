<?php

namespace App\Http\Requests;

use App\Support\AsistenteAnioEscolar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

// Validaciones del paso "Lapsos" del asistente de año nuevo: todos los lapsos del año de una vez.
class AsistenteLapsosRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    public function rules(): array
    {
        $anio = AsistenteAnioEscolar::anioActivo();
        // Normalmente ya la creó la pantalla del paso; si no (por ejemplo, una pestaña vieja), se crea aquí.
        $cantidad = $anio ? AsistenteAnioEscolar::asegurarConfiguracion($anio)->num_lapsos : 0;
        $inicio = $anio?->inicio_anio_escolar?->toDateString();
        $cierre = $anio?->cierre_anio_escolar?->toDateString();

        return [
            'lapsos' => ['required', 'array', "size:{$cantidad}"],
            'lapsos.*.nombre' => ['required', 'string', 'max:50'],
            'lapsos.*.fecha_inicio' => ['required', 'date', "after_or_equal:{$inicio}", "before_or_equal:{$cierre}"],
            'lapsos.*.fecha_fin' => ['required', 'date', "before_or_equal:{$cierre}"],
            'lapsos.*.fecha_limite_carga' => ['required', 'date'],
        ];
    }

    /** Cada lapso termina después de empezar, y el siguiente empieza después de que termina el anterior. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $lapsos = array_values($this->input('lapsos', []));
            foreach ($lapsos as $i => $lapso) {
                if ($lapso['fecha_fin'] <= $lapso['fecha_inicio']) {
                    $validator->errors()->add("lapsos.{$i}.fecha_fin", 'La fecha de fin debe ser posterior a la fecha de inicio.');
                }
                if ($lapso['fecha_limite_carga'] < $lapso['fecha_fin']) {
                    $validator->errors()->add("lapsos.{$i}.fecha_limite_carga", 'La fecha límite de carga no puede ser anterior al fin del lapso.');
                }
                if ($i > 0 && $lapso['fecha_inicio'] <= $lapsos[$i - 1]['fecha_fin']) {
                    $anterior = $lapsos[$i - 1]['nombre'] ?? 'el lapso anterior';
                    $validator->errors()->add("lapsos.{$i}.fecha_inicio", "Debe empezar después de que termine {$anterior}.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'lapsos.size' => 'Debes cargar exactamente :size lapsos, como dice la configuración de evaluación.',
            'lapsos.*.nombre.required' => 'Debes indicar el nombre del lapso.',
            'lapsos.*.nombre.max' => 'El nombre no puede pasar de 50 caracteres.',
            'lapsos.*.fecha_inicio.required' => 'Debes indicar la fecha de inicio.',
            'lapsos.*.fecha_inicio.after_or_equal' => 'No puede empezar antes del inicio del año escolar.',
            'lapsos.*.fecha_inicio.before_or_equal' => 'No puede empezar después del cierre del año escolar.',
            'lapsos.*.fecha_fin.required' => 'Debes indicar la fecha de fin.',
            'lapsos.*.fecha_fin.before_or_equal' => 'No puede terminar después del cierre del año escolar.',
            'lapsos.*.fecha_limite_carga.required' => 'Debes indicar la fecha límite de carga de notas.',
            'lapsos.*.*.date' => 'Debe ser una fecha válida.',
        ];
    }
}
