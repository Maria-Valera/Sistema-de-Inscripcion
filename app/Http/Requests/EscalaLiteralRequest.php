<?php

namespace App\Http\Requests;

use App\Models\EscalaLiteral;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EscalaLiteralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('letra')) {
            $this->merge([
                'letra' => strtoupper(trim((string) $this->letra)),
            ]);
        }
    }

    public function rules(): array
    {
        $escalaLiteral = $this->route('escala_literal');
        $escalaLiteralId = $escalaLiteral instanceof EscalaLiteral ? $escalaLiteral->id : $escalaLiteral;
        $anioId = AnioEscolarSeleccionado::id();

        return [
            'letra' => [
                'required',
                'string',
                'size:1',
                Rule::unique('escala_literals', 'letra')
                    ->where('anio_escolar_id', $anioId)
                    ->ignore($escalaLiteralId),
            ],
            'descripcion' => ['nullable', 'string', 'max:60'],
            'nota_desde' => ['required', 'integer', 'min:1', 'max:20'],
            'nota_hasta' => ['required', 'integer', 'min:1', 'max:20', 'gte:nota_desde'],
        ];
    }

    public function messages(): array
    {
        return [
            'letra.required' => 'La letra de la escala es obligatoria.',
            'letra.size' => 'La letra debe ser un único carácter (ej. A, B, C, D).',
            'letra.unique' => 'Ya existe una escala con esta letra para el año escolar seleccionado.',
            'descripcion.max' => 'La descripción no debe exceder los 60 caracteres.',
            'nota_desde.required' => 'La nota mínima es obligatoria.',
            'nota_desde.integer' => 'La nota mínima debe ser un número entero.',
            'nota_desde.min' => 'La nota mínima no puede ser menor a 1.',
            'nota_desde.max' => 'La nota mínima no puede ser mayor a 20.',
            'nota_hasta.required' => 'La nota máxima es obligatoria.',
            'nota_hasta.integer' => 'La nota máxima debe ser un número entero.',
            'nota_hasta.min' => 'La nota máxima no puede ser menor a 1.',
            'nota_hasta.max' => 'La nota máxima no puede ser mayor a 20.',
            'nota_hasta.gte' => 'La nota máxima debe ser mayor o igual a la nota mínima.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('nota_desde') || $validator->errors()->has('nota_hasta')) {
                    return;
                }

                $anioId = AnioEscolarSeleccionado::id();
                $escalaLiteral = $this->route('escala_literal');
                $escalaLiteralId = $escalaLiteral instanceof EscalaLiteral ? $escalaLiteral->id : $escalaLiteral;

                $notaDesde = (int) $this->input('nota_desde');
                $notaHasta = (int) $this->input('nota_hasta');

                // Regla de negocio: verificar solapamiento con escalas existentes del mismo año
                $solapada = EscalaLiteral::where('anio_escolar_id', $anioId)
                    ->when($escalaLiteralId, fn ($q) => $q->where('id', '!=', $escalaLiteralId))
                    ->where('nota_desde', '<=', $notaHasta)
                    ->where('nota_hasta', '>=', $notaDesde)
                    ->first();

                if ($solapada) {
                    $validator->errors()->add(
                        'nota_desde',
                        "El rango de notas se cruza con la escala [{$solapada->letra}] existente ({$solapada->nota_desde} - {$solapada->nota_hasta})."
                    );
                }
            },
        ];
    }
}

