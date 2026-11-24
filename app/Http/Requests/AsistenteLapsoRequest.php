<?php

namespace App\Http\Requests;

use App\Support\AsistenteAnioEscolar;
use Illuminate\Foundation\Http\FormRequest;

// Paso 2 del asistente de año nuevo: el lapso 1 (los demás se crean después en el CRUD de Lapsos).
class AsistenteLapsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso ya lo revisa el middleware de la ruta.
        return true;
    }

    public function rules(): array
    {
        // Las fechas del año escrito en el paso 1 (todavía no está guardado).
        $anio = AsistenteAnioEscolar::borrador()['anio'] ?? [];
        $inicio = $anio['inicio_anio_escolar'] ?? null;
        $cierre = $anio['cierre_anio_escolar'] ?? null;

        return [
            'nombre' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date', "after_or_equal:{$inicio}", "before_or_equal:{$cierre}"],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio', "before_or_equal:{$cierre}"],
            'fecha_limite_carga' => ['required', 'date', 'after_or_equal:fecha_fin'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Debes indicar el nombre del lapso.',
            'nombre.max' => 'El nombre no puede pasar de 50 caracteres.',
            'fecha_inicio.required' => 'Debes indicar la fecha de inicio.',
            'fecha_inicio.after_or_equal' => 'No puede empezar antes del inicio del año escolar.',
            'fecha_inicio.before_or_equal' => 'No puede empezar después del cierre del año escolar.',
            'fecha_fin.required' => 'Debes indicar la fecha de fin.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'fecha_fin.before_or_equal' => 'No puede terminar después del cierre del año escolar.',
            'fecha_limite_carga.required' => 'Debes indicar la fecha límite de carga de notas.',
            'fecha_limite_carga.after_or_equal' => 'La fecha límite de carga no puede ser anterior al fin del lapso.',
            '*.date' => 'Debe ser una fecha válida.',
        ];
    }
}
