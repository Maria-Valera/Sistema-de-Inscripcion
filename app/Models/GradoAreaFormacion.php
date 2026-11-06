<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GradoAreaFormacion extends Model
{
    protected $table = 'grado_area_formacions';

    protected $fillable = [
        'codigo',
        'pensum_id',
        'grado_id',
        'area_formacion_id',
        'horas',
        'nota_aprobatoria',
        'escala',
        'componente',
        'orden',
        'status',
    ];

    protected $casts = [
        'horas'            => 'integer',
        'nota_aprobatoria' => 'decimal:2',
        'orden'            => 'integer',
        'status'           => 'boolean',
    ];

    /**
     * Plan de estudio (pensum) al que pertenece.
     */
    public function pensum(): BelongsTo
    {
        return $this->belongsTo(Pensum::class, 'pensum_id', 'id');
    }

    /**
     * Grado o año escolar (1ero a 5to año).
     */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'id');
    }

    /**
     * Área de formación o asignatura.
     */
    public function area_formacion(): BelongsTo
    {
        return $this->belongsTo(AreaFormacion::class, 'area_formacion_id', 'id');
    }

    /**
     * Scope para filtrar por pensum.
     */
    public function scopeDelPensum($query, $pensumId)
    {
        return $query->where('pensum_id', $pensumId);
    }

    /**
     * Scope para filtrar por grado.
     */
    public function scopeDelGrado($query, $gradoId)
    {
        return $query->where('grado_id', $gradoId);
    }

    /**
     * Scope para ordenar según la secuencia oficial.
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('id', 'asc');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->codigo)) {
                $model->generarCodigo();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['grado_id', 'area_formacion_id']) && empty($model->codigo)) {
                $model->generarCodigo();
            }
        });
    }

    protected function generarCodigo(): void
    {
        $gradoNumero = null;
        if ($this->relationLoaded('grado') && $this->grado) {
            $gradoNumero = $this->grado->numero_grado;
        } else {
            $grado = Grado::find($this->grado_id);
            $gradoNumero = $grado?->numero_grado ?? 0;
        }
        $gradoNumero = (int) $gradoNumero;
        if ($gradoNumero < 0 || $gradoNumero > 999) {
            $gradoNumero = 0;
        }
        $nombreArea = null;
        if ($this->relationLoaded('area_formacion') && $this->area_formacion) {
            $nombreArea = $this->area_formacion->nombre_area_formacion;
        } else {
            $area = AreaFormacion::find($this->area_formacion_id);
            $nombreArea = $area?->nombre_area_formacion ?? '';
        }
        $palabras = preg_split('/\s+/', trim($nombreArea));
        $iniciales = '';
        foreach ($palabras as $palabra) {
            $iniciales .= mb_substr($palabra, 0, 1, 'UTF-8');
        }
        $prefix = Str::ascii($iniciales);
        $prefix = preg_replace('/[^A-Za-z]/u', '', $prefix);
        $prefix = Str::upper($prefix ?? '');
        if (strlen($prefix) >= 3) {
            $prefix = substr($prefix, 0, 3);
        } else {
            $nombreSinEspacios = preg_replace('/\s+/', '', $nombreArea);
            $nombreSinEspacios = Str::upper(Str::ascii($nombreSinEspacios));
            $prefix = str_pad($prefix, 3, substr($nombreSinEspacios, strlen($prefix), 3 - strlen($prefix)));
            $prefix = str_pad($prefix, 3, 'X');
        }
        $codigoBase = sprintf(
            '%d-%03d-%s',
            $gradoNumero,
            $gradoNumero,
            $prefix
        );
        $contador = 1;
        $codigoFinal = $codigoBase;
        while (self::where('codigo', $codigoFinal)->where('id', '!=', $this->id ?? 0)->exists()) {
            $codigoFinal = $codigoBase . $contador;
            $contador++;
        }
        $this->codigo = $codigoFinal;
    }
}
