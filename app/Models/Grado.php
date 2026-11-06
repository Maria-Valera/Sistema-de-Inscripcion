<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;
    protected $table = 'grados';

    protected $fillable = [
        'numero_grado',
        'capacidad_max',
        'min_seccion',
        'max_seccion',
        'status',
    ];

    public function areas_formacion()
    {
        return $this->belongsToMany(AreaFormacion::class, 'grado_area_formacions')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function gradoAreaFormacion()
    {
        return $this->hasMany(GradoAreaFormacion::class, 'grado_id', 'id');
    }

    public function inscripciones()
    {
        return $this->hasMany(\App\Models\Inscripcion::class, 'grado_id');
    }

    /**
     * Nombre legible del año escolar (ej. 1er Año, 2do Año...).
     */
    public function getNombreGradoAttribute(): string
    {
        $val = trim((string) ($this->numero_grado ?? ''));
        if ($val === '') {
            return 'Grado #' . ($this->id ?? '');
        }

        return match ($val) {
            '1', '1er Año', '1ero', '1er' => '1er Año',
            '2', '2do Año', '2do'         => '2do Año',
            '3', '3er Año', '3ero', '3er' => '3er Año',
            '4', '4to Año', '4to'         => '4to Año',
            '5', '5to Año', '5to'         => '5to Año',
            default                       => str_contains(mb_strtolower($val), 'año') || str_contains(mb_strtolower($val), 'grado')
                                                ? $val
                                                : $val . '° Año',
        };
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre_grado;
    }
}
