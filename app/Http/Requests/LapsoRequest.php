<?php

namespace App\Http\Requests;

use App\Enums\EstadoLapso;
use App\Models\AnioEscolar;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Lapso;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// Validaciones de M4. Las usan store() y update() de LapsoController.
class LapsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    /** Año del lapso: el del lapso que se edita o, al crear, el año seleccionado. */
    public function anioEscolar(): ?AnioEscolar
    {
        $lapso = $this->route('lapso');

        return $lapso ? $lapso->anioEscolar : AnioEscolarSeleccionado::modelo();
    }

    public function rules(): array
    {
        $lapso = $this->route('lapso');
        $anio = $this->anioEscolar();
        $config = ConfiguracionEvaluacion::where('anio_escolar_id', $anio?->id)->first();
        $maxLapsos = $config?->num_lapsos ?? 0;

        // Sin año o sin configuración (M3) no hay contra qué validar: se avisa y no se guarda.
        if (! $anio || ! $config) {
            return ['configuracion' => ['required']];
        }

        return [
            'numero' => [
                'required', 'integer', 'min:1', "max:{$maxLapsos}",
                Rule::unique('lapsos', 'numero')
                    ->where('anio_escolar_id', $anio->id)
                    ->ignore($lapso?->id),
            ],
            'nombre' => ['required', 'string', 'max:50'],
            'fecha_inicio' => [
                'required', 'date',
                'after_or_equal:' . $anio->inicio_anio_escolar->toDateString(),
                'before_or_equal:' . $anio->cierre_anio_escolar->toDateString(),
            ],
            'fecha_fin' => [
                'required', 'date', 'after:fecha_inicio',
                'before_or_equal:' . $anio->cierre_anio_escolar->toDateString(),
            ],
            'fecha_limite_carga' => ['required', 'date', 'after_or_equal:fecha_fin'],
            'estado' => ['required', Rule::enum(EstadoLapso::class)],
        ];
    }

    /** Reglas que miran otros registros: lapsos que se cruzan y suma de los cortes. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $lapso = $this->route('lapso');
            $anio = $this->anioEscolar();

            // Las fechas no se pueden cruzar con las de otro lapso del mismo año.
            $cruce = Lapso::where('anio_escolar_id', $anio->id)
                ->when($lapso, fn ($q) => $q->where('id', '!=', $lapso->id))
                ->where('fecha_inicio', '<=', $this->input('fecha_fin'))
                ->where('fecha_fin', '>=', $this->input('fecha_inicio'))
                ->first();

            if ($cruce) {
                $validator->errors()->add('fecha_inicio', "Las fechas se cruzan con las de {$cruce->nombre} ({$cruce->fecha_inicio->format('d/m/Y')} al {$cruce->fecha_fin->format('d/m/Y')}).");
            }

                        // Al editar, los cortes que ya tiene el lapso tienen que seguir dentro de sus nuevas fechas.
            if ($lapso) {
                $fuera = $lapso->cortes()
                    ->where(fn ($q) => $q->where('fecha_inicio', '<', $this->input('fecha_inicio'))
                        ->orWhere('fecha_fin', '>', $this->input('fecha_fin')))
                    ->get();

                if ($fuera->isNotEmpty()) {
                    $lista = $fuera->map(fn ($c) => "{$c->nombre} ({$c->fecha_inicio?->format('d/m/Y')} al {$c->fecha_fin?->format('d/m/Y')})")->join(', ');
                    $validator->errors()->add('fecha_fin', "Con esas fechas quedarían cortes fuera del lapso: {$lista}. Ajusta primero las fechas de esos cortes.");
                }
            }

                        // Un lapso solo se abre si sus cortes ya suman el total del lapso (4 cortes de 20 = 80).
            if ($this->input('estado') === EstadoLapso::Abierto->value) {
                $totalLapso = ConfiguracionEvaluacion::where('anio_escolar_id', $anio->id)->first()->puntosPorLapso();
                $suma = $lapso ? (float) $lapso->cortes()->sum('peso_lapso') : 0;
                $texto = fn ($n) => rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');

                if (abs($suma - $totalLapso) > 0.001) {
                    $validator->errors()->add('estado', "Para abrir el lapso, sus cortes deben sumar {$texto($totalLapso)} puntos (ahora suman {$texto($suma)}).");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'configuracion.required' => 'El año escolar no tiene configuración de evaluación: créala primero en Configuración de Evaluación.',
            'numero.required' => 'Debes indicar el número del lapso.',
            'numero.min' => 'El número del lapso debe ser como mínimo 1.',
            'numero.max' => 'El número del lapso no puede pasar del número de lapsos configurado (:max).',
            'numero.unique' => 'Ya existe un lapso con ese número en este año escolar.',
            'nombre.required' => 'Debes indicar el nombre del lapso.',
            'nombre.max' => 'El nombre no puede pasar de 50 caracteres.',
            'fecha_inicio.required' => 'Debes indicar la fecha de inicio.',
            'fecha_inicio.after_or_equal' => 'El lapso no puede empezar antes del inicio del año escolar.',
            'fecha_inicio.before_or_equal' => 'El lapso no puede empezar después del cierre del año escolar.',
            'fecha_fin.required' => 'Debes indicar la fecha de fin.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'fecha_fin.before_or_equal' => 'El lapso no puede terminar después del cierre del año escolar.',
            'fecha_limite_carga.required' => 'Debes indicar la fecha límite de carga de notas.',
            'fecha_limite_carga.after_or_equal' => 'La fecha límite de carga no puede ser anterior al fin del lapso.',
            'estado.enum' => 'El estado elegido no es válido.',
            '*.integer' => 'Debe ser un número entero.',
            '*.date' => 'Debe ser una fecha válida.',
        ];
    }
}
