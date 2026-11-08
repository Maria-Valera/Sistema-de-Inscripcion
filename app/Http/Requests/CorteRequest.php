<?php

namespace App\Http\Requests;

use App\Enums\EstadoCorte;
use App\Enums\EstadoLapso;
use App\Models\ConfiguracionEvaluacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// Validaciones de M5. Las usan store() y update() de CorteController.
class CorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    public function rules(): array
    {
        $lapso = $this->route('lapso');
        $corte = $this->route('corte');
        $config = ConfiguracionEvaluacion::where('anio_escolar_id', $lapso->anio_escolar_id)->first();
        $maxCortes = $config?->num_cortes ?? 0;
        $notaMaxima = $config?->nota_maxima ?? 0;
        $inicioLapso = $lapso->fecha_inicio->toDateString();
        $finLapso = $lapso->fecha_fin->toDateString();

        return [
            'numero' => [
                'required', 'integer', 'min:1', "max:{$maxCortes}",
                Rule::unique('cortes', 'numero')->where('lapso_id', $lapso->id)->ignore($corte?->id),
            ],
            'nombre' => ['required', 'string', 'max:50'],
            'peso_lapso' => ['required', 'numeric', 'gt:0', "max:{$notaMaxima}"],
            'fecha_inicio' => ['nullable', 'date', "after_or_equal:{$inicioLapso}", "before_or_equal:{$finLapso}"],
            'fecha_fin' => ['nullable', 'date', "after_or_equal:{$inicioLapso}", "before_or_equal:{$finLapso}"],
            'fecha_limite_carga' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoCorte::class)],
        ];
    }

    /** Reglas que miran otros registros: lapso cerrado, orden de fechas y suma de pesos. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $lapso = $this->route('lapso');
            $corte = $this->route('corte');

            if ($lapso->estado === EstadoLapso::Cerrado) {
                $validator->errors()->add('estado', 'El lapso está cerrado: sus cortes ya no se pueden modificar.');
                return;
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $inicio = $this->input('fecha_inicio');
            $fin = $this->input('fecha_fin');
            if ($inicio && $fin && $fin < $inicio) {
                $validator->errors()->add('fecha_fin', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
            }
            if ($fin && $this->input('fecha_limite_carga') < $fin) {
                $validator->errors()->add('fecha_limite_carga', 'La fecha límite de carga no puede ser anterior al fin del corte.');
            }

            // La suma de los pesos de los cortes del lapso no puede pasar de la nota máxima (20).
            $notaMaxima = (float) ConfiguracionEvaluacion::where('anio_escolar_id', $lapso->anio_escolar_id)->value('nota_maxima');
            $otros = (float) $lapso->cortes()->when($corte, fn ($q) => $q->where('id', '!=', $corte->id))->sum('peso_lapso');
            $disponible = $notaMaxima - $otros;

            if ((float) $this->input('peso_lapso') - $disponible > 0.001) {
                $texto = rtrim(rtrim(number_format($disponible, 2, '.', ''), '0'), '.');
                $validator->errors()->add('peso_lapso', "Los cortes de este lapso no pueden sumar más de {$notaMaxima} puntos: a este corte le quedan como máximo {$texto}.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'Debes indicar el número del corte.',
            'numero.min' => 'El número del corte debe ser como mínimo 1.',
            'numero.max' => 'El número del corte no puede pasar del número de cortes configurado (:max).',
            'numero.unique' => 'Ya existe un corte con ese número en este lapso.',
            'nombre.required' => 'Debes indicar el nombre del corte.',
            'nombre.max' => 'El nombre no puede pasar de 50 caracteres.',
            'peso_lapso.required' => 'Debes indicar cuántos puntos vale el corte.',
            'peso_lapso.numeric' => 'Los puntos deben ser un número.',
            'peso_lapso.gt' => 'El corte debe valer más de 0 puntos.',
            'peso_lapso.max' => 'Un corte no puede valer más de :max puntos.',
            'fecha_inicio.after_or_equal' => 'El corte no puede empezar antes que el lapso.',
            'fecha_inicio.before_or_equal' => 'El corte no puede empezar después de que termine el lapso.',
            'fecha_fin.after_or_equal' => 'El corte no puede terminar antes de que empiece el lapso.',
            'fecha_fin.before_or_equal' => 'El corte no puede terminar después del lapso.',
            'fecha_limite_carga.required' => 'Debes indicar la fecha límite de carga de notas.',
            'estado.enum' => 'El estado elegido no es válido.',
            '*.integer' => 'Debe ser un número entero.',
            '*.date' => 'Debe ser una fecha válida.',
        ];
    }
}
